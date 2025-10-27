<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends FormRequest
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
        $provider = $this->input('provider');

        // ✅ Check if provider is email
        if (filter_var($provider, FILTER_VALIDATE_EMAIL)) {
            return [
                'provider' => [
                    'required',
                    'email',
                    Rule::exists('users', 'email')->where(function ($query) {
                        $query->where('is_verified', true)
                            ->where('status', 'active');
                    }),
                ],
            ];
        }

        // ✅ Else assume it's mobile
        return [
            'provider' => [
                'required',
                'digits:10',
                Rule::exists('users', 'mobile')->where(function ($query) {
                    $query->where('is_verified', true)
                        ->where('status', 'active');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => 'Email or mobile is required.',
            'provider.email'    => 'Enter a valid email address.',
            'provider.exists'   => 'No verified and active account found with this email or mobile number.',
            'provider.digits'   => 'Mobile number must be 10 digits.',
        ];
    }
}
