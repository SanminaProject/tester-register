<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixtureRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:100|unique:fixtures,serial_number',
            'tester_id' => 'required|exists:testers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.unique' => 'Serial number already exists',
            'tester_id.exists' => 'Selected tester does not exist',
            'status.in' => 'Status must be either active or inactive',
        ];
    }
}
