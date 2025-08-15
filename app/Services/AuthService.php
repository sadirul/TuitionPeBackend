<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'tuition_name' => $data['tuition_name'],
            'name'         => $data['name'],
            'username'     => $data['username'],
            'mobile'       => $data['mobile'],
            'address'      => $data['address'],
            'role'         => 'tuition',
            'email'        => $data['email'],
            'expiry_datetime' => now()->addDays(3),
            'password'     => $data['password'],
        ]);
        $user->update([
            'tuition_id' => $user->id
        ]);

        return [
            'status' => 'success',
            'msg' => 'User registered successfully',
        ];
    }


    public function login($request)
    {
        $credentials = $request->only('username', 'password');
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid credentials!'], 401);
        }

        $user = Auth::guard('api')->user()->load(['studentInfo']);
        if ($user->status !== 'active') {
            return response()->json(['status' => 'error', 'msg' => 'Account Inactive!'], 401);
        }

        $expirationDate = Carbon::parse($user->expiry_datetime);
        if ($expirationDate->isPast()) {
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
