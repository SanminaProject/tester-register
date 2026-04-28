<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\TesterSparePart;
use Illuminate\Validation\Rule;

class SparePartForm extends Form
{
    public ?TesterSparePart $sparePart = null;

    public string $name = '';
    public ?string $manufacturer_part_number = null;
    public ?int $quantity_in_stock = null;
    public ?int $reorder_level = null;
    public ?string $last_order_date = null;
    public ?float $unit_price = null;
    public ?string $description = null;
    public ?int $tester_id = null;
    public ?int $supplier_id = null;
    public array $responsible_user_ids = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'manufacturer_part_number' => 'nullable|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'last_order_date' => 'nullable|date',
            'unit_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'tester_id' => 'required|exists:testers,id',
            'supplier_id' => 'nullable|exists:tester_spare_part_suppliers,id',
            'responsible_user_ids' => 'array',
            'responsible_user_ids.*' => 'exists:users,id',
        ];
    }

    public function save()
    {
        $this->validate();

        $sparePart = TesterSparePart::create($this->only([
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

        $sparePart->responsibleUsers()->sync($this->responsible_user_ids);

        $this->reset();
    }

    public function setSparePart(TesterSparePart $sparePart)
    {
        $this->sparePart = $sparePart;

        $this->fill([
            ...$sparePart->toArray(),
            'last_order_date' => $sparePart->last_order_date
                ? $sparePart->last_order_date->format('Y-m-d')
                : null,
        ]);

        $this->responsible_user_ids = $sparePart->responsibleUsers()->pluck('id')->toArray();
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

        $this->sparePart->responsibleUsers()->sync($this->responsible_user_ids);
    }
}