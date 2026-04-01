<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalibrationScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scheduled_date' => 'sometimes|date|after_or_equal:today',
            'procedure' => 'sometimes|string|min:3',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_date.after_or_equal' => 'Scheduled date must be today or later',
            'procedure.min' => 'Procedure description must be at least 3 characters',
        ];
    }
}
