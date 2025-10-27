<?php

namespace App\Http\Controllers;

use App\Helpers\SmsHelper;
use App\Http\Requests\PasswordValidateRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function reset(PasswordValidateRequest $request)
    {
        $provider = $request->input('provider');
        $isEmail = filter_var($provider, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            // ✅ Email-based password reset (Laravel default)
            $status = Password::reset(
                [
                    'email' => $provider,
                    'password' => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                    'token' => $request->token,
                ],
                function ($user, $password) {
                    $user->password = $password;
                    $user->setRememberToken(Str::random(60));
                    $user->save();
                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET
                ? response()->json(['status' => 'success', 'msg' => __($status)])
                : response()->json(['status' => 'error', 'msg' => __($status)], 400);
        }

        // ✅ Mobile-based password reset using OTP
        $record = DB::table('password_reset_tokens')
            ->where('email', $provider) // saving mobile in email column
            ->where('token', $request->token) // OTP is stored in token column
            ->first();

        if (!$record) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid OTP'], 400);
        }

        // Check OTP expiry (10 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['status' => 'error', 'msg' => 'OTP expired'], 400);
        }

        $user = User::where('mobile', $provider)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'msg' => 'User not found'], 404);
        }

        // ✅ Update password manually
        $user->password = $request->password;
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Delete used OTP
        DB::table('password_reset_tokens')->where('email', $provider)->delete();

        return response()->json(['status' => 'success', 'msg' => 'Password reset successfully']);
    }
}
