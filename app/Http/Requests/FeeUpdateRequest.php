<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FeeUpdateRequest extends FormRequest
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
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'fee_uuid' => $this->route('fee_uuid'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fee_uuid' => [
                'required',
                Rule::exists('fees', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'is_paid' => ['required', 'boolean'],
        ];
    }
}
