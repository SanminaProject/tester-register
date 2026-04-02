<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceScheduleRequest extends FormRequest
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
            'tester_id' => ['sometimes', 'integer', 'exists:testers,id'],
            'scheduled_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:scheduled,completed,overdue'],
            'procedure' => ['sometimes', 'string', 'min:3', 'max:1000'],
            'completed_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'performed_by' => ['sometimes', 'nullable', 'string', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
