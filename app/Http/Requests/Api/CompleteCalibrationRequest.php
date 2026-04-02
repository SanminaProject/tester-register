<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CompleteCalibrationRequest extends FormRequest
{
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
            'completed_date' => ['required', 'date', 'before_or_equal:today'],
            'performed_by' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
