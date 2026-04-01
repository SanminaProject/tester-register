<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalibrationScheduleRequest extends FormRequest
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
            'tester_id' => 'required|exists:testers,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'procedure' => 'required|string|min:3',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tester_id.exists' => 'Selected tester does not exist',
            'scheduled_date.after_or_equal' => 'Scheduled date must be today or later',
            'procedure.min' => 'Procedure description must be at least 3 characters',
        ];
    }
}
