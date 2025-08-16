<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'class' => [
                'required',
                Rule::exists('classes', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'gender' => 'required|in:male,female,others',
            'mobile' => 'required|digits:10',
            'address' => 'required|string|max:255',
            'guardianName' => 'required|string|max:255',
            'guardianMobile' => 'required|digits:10',
            'monthlyFees' => 'required|numeric|min:0',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
        ];
    }
}
