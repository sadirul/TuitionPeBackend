<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
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
        // Add the UUID from the route into the request data so validation can see it
        $this->merge(['uuid' => $this->route('uuid')]);

        return [
            'uuid' => [
                'required',
                Rule::exists('classes', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
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
                })->ignore($this->route('uuid'), 'uuid'), // Ignore current record by UUID
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
