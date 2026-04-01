<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTesterRequest extends FormRequest
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
            'model' => 'required|string|max:100',
            'serial_number' => 'required|string|unique:testers|max:50',
            'customer_id' => 'required|exists:tester_customers,id',
            'purchase_date' => 'required|date',
            'status' => 'in:active,inactive,maintenance',
            'location' => 'nullable|string',
        ];
    }
}
