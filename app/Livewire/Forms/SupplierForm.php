<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\TesterSparePartSupplier;

class SupplierForm extends Form
{
    public ?TesterSparePartSupplier $supplier = null;

    public string $supplier_name = '';
    public string $contact_person = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $address = '';

    protected function rules()
    {
        return [
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ];
    }

    public function save()
    {
        $this->validate();

        TesterSparePartSupplier::create($this->only([
            'supplier_name',
            'contact_person',
            'contact_email',
            'contact_phone',
            'address',
        ]));

        $this->reset();
    }

    public function setSupplier(TesterSparePartSupplier $supplier)
    {
        $this->supplier = $supplier;

        $this->fill($supplier->toArray());
    }

    public function update()
    {
        $this->validate();

        $this->supplier->update($this->only([
            'supplier_name',
            'contact_person',
            'contact_email',
            'contact_phone',
            'address',
        ]));
    }
}