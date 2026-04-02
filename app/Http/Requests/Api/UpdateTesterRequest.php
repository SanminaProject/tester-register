<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTesterRequest extends FormRequest
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
        $testerRoute = $this->route('tester');
        $testerId = is_object($testerRoute) ? $testerRoute->getKey() : $testerRoute;

        return [
            'customer_id' => ['sometimes', 'integer', 'exists:tester_customers,id'],
            'model' => ['sometimes', 'string', 'max:100'],
            'serial_number' => ['sometimes', 'string', 'max:100', Rule::unique('testers', 'serial_number')->ignore($testerId)],
            'purchase_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,maintenance'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
