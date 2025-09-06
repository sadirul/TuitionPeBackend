<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AddStudentFeesRequest extends FormRequest
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
        return [
            'student_id' => [
                'required',
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('tuition_id', $this->user()->id);
                }),
            ],
            'year_month' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    // Expect format "Month YYYY"
                    $parts = explode(' ', $value);
                    if (count($parts) !== 2) {
                        return $fail("The $attribute must be in 'Month YYYY' format.");
                    }

                    [$month, $year] = $parts;

                    // Validate month
                    if (!in_array(Str::ucfirst(strtolower($month)), [
                        'January',
                        'February',
                        'March',
                        'April',
                        'May',
                        'June',
                        'July',
                        'August',
                        'September',
                        'October',
                        'November',
                        'December'
                    ])) {
                        return $fail("The $attribute contains an invalid month.");
                    }

                    // Validate year ≤ current year
                    if (!is_numeric($year) || $year > date('Y')) {
                        return $fail("The year in $attribute cannot be greater than the current year.");
                    }
                },
            ],
            'is_paid' => 'required|boolean',
        ];
    }
}
