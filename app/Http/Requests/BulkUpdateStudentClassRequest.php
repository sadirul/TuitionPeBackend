<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class BulkUpdateStudentClassRequest extends FormRequest
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
            'class' => [
                'required',
                Rule::exists('classes', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'student_ids'   => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'required',
                Rule::exists('students', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Class ID is required',
            'class_id.exists'   => 'Invalid class selected',
            'student_ids.required' => 'At least one student must be selected',
            'student_ids.*.exists' => 'One or more student IDs are invalid',
        ];
    }
}
