<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListFixtureRequest extends FormRequest
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
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'tester_id' => 'sometimes|integer|exists:testers,id',
            'status' => 'sometimes|in:active,inactive',
            'search' => 'sometimes|string|max:255',
        ];
    }
}
