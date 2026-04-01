<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTesterRequest extends FormRequest
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
        $testerId = $this->route('tester')?->id;

        return [
            'model' => 'string|max:100',
            'serial_number' => ['string', 'max:50', Rule::unique('testers', 'serial_number')->ignore($testerId)],
            'customer_id' => 'exists:tester_customers,id',
            'purchase_date' => 'date',
            'status' => 'in:active,inactive,maintenance',
            'location' => 'nullable|string',
        ];
    }
}
