<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSparePartRequest extends FormRequest
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
        $partId = $this->route('part')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'part_number' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('spare_parts', 'part_number')->ignore($partId),
            ],
            'quantity_in_stock' => 'sometimes|integer|min:0',
            'unit_cost' => 'sometimes|numeric|min:0|max:999999.99',
            'supplier' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'part_number.unique' => 'Part number already exists',
            'quantity_in_stock.min' => 'Quantity in stock cannot be negative',
            'unit_cost.max' => 'Unit cost exceeds maximum allowed value',
        ];
    }
}
