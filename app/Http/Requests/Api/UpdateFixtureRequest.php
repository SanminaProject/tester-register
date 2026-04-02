<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFixtureRequest extends FormRequest
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
        $fixtureRoute = $this->route('fixture');
        $fixtureId = is_object($fixtureRoute) ? $fixtureRoute->getKey() : $fixtureRoute;

        return [
            'tester_id' => ['sometimes', 'integer', 'exists:testers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'string', 'max:100', Rule::unique('fixtures', 'serial_number')->ignore($fixtureId)],
            'purchase_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,maintenance'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
