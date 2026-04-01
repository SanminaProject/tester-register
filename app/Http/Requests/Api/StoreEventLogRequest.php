<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventLogRequest extends FormRequest
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
            'type' => 'required|in:maintenance,calibration,issue,repair,other',
            'description' => 'required|string|min:5|max:1000',
            'event_date' => 'required|date_format:Y-m-d H:i:s|before_or_equal:now',
        ];
    }

    public function messages(): array
    {
        return [
            'tester_id.exists' => 'Selected tester does not exist',
            'type.in' => 'Invalid event type. Allowed types: maintenance, calibration, issue, repair, other',
            'description.min' => 'Event description must be at least 5 characters',
            'event_date.before_or_equal' => 'Event date cannot be in the future',
            'event_date.date_format' => 'Event date must be in format Y-m-d H:i:s (e.g., 2026-04-02 14:30:00)',
        ];
    }
}
