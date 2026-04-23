<?php

namespace App\Livewire\Pages\Inventory\Suppliers;

use App\Models\TesterSparePartSupplier;
use Livewire\Component;
use App\Models\DataChangeLog;

class SupplierDetails extends Component
{
    public TesterSparePartSupplier $sparePartSupplier;

    public function mount($sparePartSupplierId)
    {
        $this->sparePartSupplier = TesterSparePartSupplier::findOrFail($sparePartSupplierId);
    }

    public function deleteSupplier()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $this->sparePartSupplier->delete();
        session()->flash('message', 'Spare part supplier deleted successfully.');
        $this->dispatch('switchTab', tab: 'suppliers');
    }

    public function render()
    {
        return view('livewire.pages.inventory.suppliers.supplier-details');
    }
}
