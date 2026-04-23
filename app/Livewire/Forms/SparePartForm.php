<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\TesterSparePart;
use Illuminate\Validation\Rule;

class SparePartForm extends Form
{
    public ?TesterSparePart $sparePart = null;

    public string $name = '';
    public string $manufacturer_part_number = '';
    public int $quantity_in_stock = 0;
    public int $reorder_level = 0;
    public ?string $last_order_date = null;
    public float $unit_price = 0;
    public string $description = '';
    public ?int $tester_id = null;
    public ?int $supplier_id = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'manufacturer_part_number' => 'nullable|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'last_order_date' => 'nullable|date',
            'unit_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'tester_id' => 'required|exists:testers,id',
            'supplier_id' => 'required|exists:tester_spare_part_suppliers,id',
        ];
    }

    public function save()
    {
        $this->validate();

        TesterSparePart::create($this->only([
            'name',
            'manufacturer_part_number',
            'quantity_in_stock',
            'reorder_level',
            'last_order_date',
            'unit_price',
            'description',
            'tester_id',
            'supplier_id',
        ]));

        $this->reset();
    }

    public function setSparePart(TesterSparePart $sparePart)
    {
        $this->sparePart = $sparePart;

        $this->fill($sparePart->toArray());
    }

    public function update()
    {
        $this->validate();

        $this->sparePart->update($this->only([
            'name',
            'manufacturer_part_number',
            'quantity_in_stock',
            'reorder_level',
            'last_order_date',
            'unit_price',
            'description',
            'tester_id',
            'supplier_id',
        ]));
    }
}