<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
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
        return [
            'tuitionName' => ['sometimes', 'required', 'string', 'max:255'],
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'mobile'      => ['sometimes', 'required', 'digits:10', 'numeric'],
            'address'     => ['sometimes', 'required', 'string', 'max:255'],
            'email'       => ['sometimes', 'required', 'email', 'max:255'],
            'upi_id'       => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
