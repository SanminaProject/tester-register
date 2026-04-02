<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSparePartRequest extends FormRequest
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
        $partRoute = $this->route('sparePart');
        $partId = is_object($partRoute) ? $partRoute->getKey() : $partRoute;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'part_number' => ['sometimes', 'string', 'max:100', Rule::unique('spare_parts', 'part_number')->ignore($partId)],
            'quantity_in_stock' => ['sometimes', 'integer', 'min:0'],
            'unit_cost' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'supplier' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
