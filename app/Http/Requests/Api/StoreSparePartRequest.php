<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparePartRequest extends FormRequest
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
            'part_number' => 'required|string|max:100|unique:spare_parts,part_number',
            'quantity_in_stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0|max:999999.99',
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
