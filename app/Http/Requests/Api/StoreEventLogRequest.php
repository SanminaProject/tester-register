<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventLogRequest extends FormRequest
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
            'type' => ['required', 'in:maintenance,calibration,issue,repair,other'],
            'event_date' => ['required', 'date', 'before_or_equal:now'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'performed_by' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
