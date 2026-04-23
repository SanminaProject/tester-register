<?php

namespace App\Livewire\Pages\Inventory\Suppliers;

use App\Livewire\Forms\SupplierForm;
use App\Models\TesterSparePartSupplier;
use Livewire\Component;

class SupplierLogging extends Component
{
    public SupplierForm $form;

    public ?int $sparePartSupplierId = null;
    public bool $isEdit = false;

    public function render()
    {
        return view('livewire.pages.inventory.suppliers.supplier-logging');
    }

    public function mount($sparePartSupplierId = null)
    {
        if ($sparePartSupplierId) {
            $this->sparePartSupplierId = $sparePartSupplierId;
            $this->isEdit = true;

            $supplier = TesterSparePartSupplier::findOrFail($sparePartSupplierId);

            $this->form->setSupplier($supplier);
        }
    }

    public function save()
    {
        if ($this->isEdit) {
            $this->form->update();

            session()->flash('success', 'Supplier updated successfully!');
        } else {
            $this->form->save();

            session()->flash('success', 'Supplier created successfully!');
        }

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'suppliers');
    }
}