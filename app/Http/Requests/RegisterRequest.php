<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        // Throw an exception with only the first error message
        throw new HttpResponseException(
            response()->json(['status' => 'error', 'msg' => $validator->errors()->first(), 'data' => null], 400)
        );
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // public function rules(): array
    // {
    //     return [
    //         'tuition_name' => 'required|string|max:255',
    //         'name' => 'required|string|max:255',
    //         'username' => 'required|string|max:255|unique:users',
    //         'mobile' => 'required|digits:10',
    //         'address' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //         'confirm_password' => 'required|string|min:6|same:password',
    //     ];
    // }

    public function rules(): array
    {
        $mobile = $this->input('mobile');
        $email = $this->input('email');
        $username = $this->input('username');

        // find existing user
        $existingUser = User::where(function ($q) use ($mobile, $email, $username) {
            $q->where('mobile', $mobile)
                ->orWhere('email', $email)
                ->orWhere('username', $username);
        })->first();

        // If user exists and is not verified, allow reuse
        $ignoreUnverified = $existingUser && !$existingUser->is_verified;

        return [
            'tuition_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',

            'username' => [
                'required',
                'string',
                'max:255',
                $ignoreUnverified
                    ? Rule::unique('users', 'username')->ignore($existingUser->id)
                    : Rule::unique('users', 'username')
            ],

            'mobile' => [
                'required',
                'digits:10',
                $ignoreUnverified
                    ? Rule::unique('users', 'mobile')->ignore($existingUser->id)
                    : Rule::unique('users', 'mobile')
            ],

            'address' => 'required|string|max:255',

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $ignoreUnverified
                    ? Rule::unique('users', 'email')->ignore($existingUser->id)
                    : Rule::unique('users', 'email')
            ],

            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
        ];
    }
}
