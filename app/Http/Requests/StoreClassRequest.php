<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
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
            'class_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id)
                        ->where('class_name', $this->class_name);

                    if (!is_null($this->section)) {
                        $query->where('section', $this->section);
                    } else {
                        $query->whereNull('section');
                    }
                }),
            ],
            'section' => ['nullable', 'string', 'max:255'],
        ];
    }


    public function messages(): array
    {
        return [
            'unique' => 'This class ' . ($this->section ? 'and section ' : '') . 'already exists for your tuition.',
        ];
    }
}
