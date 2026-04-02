<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixtureRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:fixtures,serial_number'],
            'purchase_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive,maintenance'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
