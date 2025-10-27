<?php

namespace App\Services;

use App\Helpers\SmsHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data)
    {
        // Check if user already exists
        $existingUser = User::where('mobile', $data['mobile'])->first();

        if ($existingUser && $existingUser->is_verified) {
            return response()->json(['status' => 'error', 'msg' => 'Mobile number already registered'], 400);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Create or update unverified user
        $user = User::updateOrCreate(
            ['mobile' => $data['mobile']],
            [
                'tuition_name'     => $data['tuition_name'] ?? null,
                'name'             => $data['name'],
                'username'         => $data['username'],
                'address'          => $data['address'] ?? null,
                'role'             => 'tuition',
                'email'            => $data['email'] ?? null,
                'expiry_datetime'  => now()->addDays(90),
                'password'         => $data['password'],
                'otp'              => $otp,
                'otp_expires_at'   => now()->addMinutes(5),
                'is_verified'      => false,
            ]
        );

        // 👉 set tuition_id same as user id (like your original code)
        if (!$user->tuition_id) {
            $user->update([
                'tuition_id' => $user->id
            ]);
        }

        // Send OTP via Fast2SMS
        $smsResponse = SmsHelper::sendSms(
            $user->mobile,
            '201012',
        );

        return response()->json([
            'status' => 'success',
            'msg' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        // Step 1: Validate input manually using Validator
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        // Step 2: Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        // Step 3: Check user existence
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'msg' => 'User not found',
            ], 404);
        }

        // Step 4: Already verified?
        if ($user->is_verified) {
            return response()->json([
                'status' => 'success',
                'msg' => 'Already verified',
            ]);
        }

        // Step 5: Check OTP validity
        if ($user->otp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Invalid OTP',
            ], 400);
        }

        // Step 6: Check OTP expiry
        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'status' => 'error',
                'msg' => 'OTP expired',
            ], 400);
        }

        // Step 7: Mark verified
        $user->update([
            'is_verified' => true,
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'msg' => 'OTP verified successfully! Redirecting...',
        ]);
    }

    public function resendOtp(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        // Step 2: Check if user exists
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'msg' => 'User not found',
            ], 404);
        }

        // Step 3: Already verified?
        if ($user->is_verified) {
            return response()->json([
                'status' => 'success',
                'msg' => 'Mobile already verified',
            ]);
        }

        // Step 4: Generate new OTP
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Step 5: Update user record
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Step 6: Send OTP using Fast2SMS helper
        SmsHelper::sendSms(
            $user->mobile,
            '201012',
            $otp
        );

        // Step 7: Return response
        return response()->json([
            'status' => 'success',
            'msg' => 'OTP resent successfully',
        ]);
    }


    public function login($request)
    {
        $credentials = $request->only('username', 'password');
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid credentials!'], 401);
        }

        $user = Auth::guard('api')->user()->load(['studentInfo',]);
        if (!$user->is_verified) {
            return response()->json(['status' => 'error', 'msg' => 'Your account is not verified!'], 403);
        }
        $messages = [
            'inactive' => 'Your account is inactive. Please contact support.',
            'deleted'  => 'Your account has been deleted!',
        ];

        if (isset($messages[$user->status])) {
            return response()->json([
                'status' => 'error',
                'msg'    => $messages[$user->status],
            ], 401);
        }


        $expirationDate = Carbon::parse($user->tuition->expiry_datetime);
        if ($user->role === 'student' && $expirationDate->isPast()) {
            return response()->json(['status' => 'error', 'msg' => 'Your plan has been expired!'], 401);
        }

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken($token, $user = null)
    {
        return response()->json([
            'status' => 'success',
            'msg' => 'Loggedin successfully',
            'data' => $user ? $user : null,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh(), null);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['status' => 'success', 'msg' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }
    }
}
