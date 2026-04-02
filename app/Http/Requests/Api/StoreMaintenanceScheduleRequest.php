<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceScheduleRequest extends FormRequest
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
            'tester_id' => ['required', 'integer', 'exists:testers,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'procedure' => ['required', 'string', 'min:3', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
