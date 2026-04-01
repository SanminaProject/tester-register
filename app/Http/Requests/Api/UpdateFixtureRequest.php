<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFixtureRequest extends FormRequest
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
        $fixtureId = $this->route('fixture')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'serial_number' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('fixtures', 'serial_number')->ignore($fixtureId),
            ],
            'tester_id' => 'sometimes|exists:testers,id',
            'purchase_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,inactive',
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
