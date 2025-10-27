<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordValidateRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'msg'    => $validator->errors()->first(),
                'data'   => null
            ], 400)
        );
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $provider = $this->input('provider'); // can be email or mobile

        // Detect type (email or mobile)
        if (filter_var($provider, FILTER_VALIDATE_EMAIL)) {
            // ✅ Email case
            return [
                'token'    => 'required|string',
                'provider' => 'required|email|exists:password_reset_tokens,email',
                'password' => 'required|string|min:6|confirmed',
            ];
        } else {
            // ✅ Mobile case
            return [
                'token'    => 'required|digits:6|exists:password_reset_tokens,token',
                'provider' => 'required|digits:10|exists:password_reset_tokens,email', // using same column for both
                'password' => 'required|string|min:6|confirmed',
            ];
        }
    }

    public function messages(): array
    {
        $provider = $this->input('provider');
        $isEmail = filter_var($provider, FILTER_VALIDATE_EMAIL);

        $type = $isEmail ? 'email' : 'mobile';
        $tokenLabel = $isEmail ? 'token' : 'OTP';

        return [
            'token.required'     => ucfirst($tokenLabel) . ' is required.',
            'token.exists'       => ucfirst($tokenLabel) . ' is invalid or expired.',
            'provider.required'  => ucfirst($type) . ' is required.',
            'provider.email'     => 'Please enter a valid email address.',
            'provider.digits'    => 'Mobile number must be 10 digits.',
            'provider.exists'    => 'No reset request found with this ' . $type . '.',
            'password.required'  => 'Password is required.',
            'password.min'       => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
