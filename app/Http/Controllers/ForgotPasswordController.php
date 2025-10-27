<?php

namespace App\Http\Controllers;

use App\Helpers\SmsHelper;
use App\Http\Requests\ForgotPasswordRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $input = $request->input('provider'); // can be email or mobile
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        // ✅ Case 1: Email-based reset (default Laravel way)
        if ($isEmail) {
            $status = Password::sendResetLink(['email' => $input]);

            return $status === Password::RESET_LINK_SENT
                ? response()->json(['status' => 'success', 'msg' => 'Reset link sent! Please check your email.', 'redirect' => false])
                : response()->json(['status' => 'error', 'msg' => 'Unable to send reset link.'], 400);
        }

        // ✅ Case 2: Mobile-based reset via OTP
        $mobile = preg_replace('/\D/', '', $input); // remove non-numeric
        if (strlen($mobile) !== 10) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid mobile number.'], 422);
        }

        $otp = rand(100000, 999999);

        // 💾 Store OTP in password_resets using "email" column to hold mobile
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $mobile], // reuse email column
            [
                'token' => $otp,
                'created_at' => Carbon::now(),
            ]
        );

        // 📲 Send OTP via Fast2SMS
        SmsHelper::sendSms(
            $mobile,
            '201014',
            $otp
        );

        return response()->json([
            'status' => 'success',
            'msg' => 'OTP sent successfully to your mobile number.',
            'redirect' => true
        ]);
    }
}
