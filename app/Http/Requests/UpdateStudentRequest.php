<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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

    public function prepareForValidation()
    {
        $this->merge([
            'student_id' => $this->route('student_id'),
        ]);
    }

    public function rules(): array
    {
        // find related user_id from student uuid
        $student = \App\Models\User::where('uuid', $this->student_id)
            ->select('id')
            ->first();

        $userId = $student?->id;

        return [
            'student_id' => [
                'required',
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'name' => 'sometimes|required|string|max:255',
            'class' => [
                'sometimes',
                'required',
                Rule::exists('classes', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'gender' => 'sometimes|required|in:male,female,others',
            'mobile' => [
                'sometimes',
                'required',
                'digits:10',
                Rule::unique('users', 'mobile')
                    ->where('tuition_id', $this->user()->id)
                    ->ignore($userId),
            ],
            'address' => 'sometimes|required|string|max:255',
            'guardianName' => 'sometimes|required|string|max:255',
            'guardianMobile' => 'sometimes|required|digits:10',
            'monthlyFees' => 'sometimes|required|numeric|min:0',
        ];
    }
}
