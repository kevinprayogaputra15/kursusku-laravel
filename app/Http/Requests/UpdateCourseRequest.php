<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Course yang sedang diedit (hasil route model binding).
        $course = $this->route('course');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                // Unik terhadap Course lain, tetapi boleh sama dengan milik sendiri.
                Rule::unique('courses', 'code')->ignore($course),
            ],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'fee' => ['required', 'integer', 'min:0'],
            'quota' => ['required', 'integer', 'between:1,500'],
            'start_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,open,closed'],
        ];
    }
}
