<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMaintenanceRequest extends FormRequest
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
            'completed_date' => 'required|date|before_or_equal:today',
            'performed_by' => 'required|string|min:2|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'completed_date.before_or_equal' => 'Completed date cannot be in the future',
            'performed_by.min' => 'Technician name must be at least 2 characters',
        ];
    }
}
