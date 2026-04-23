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

        $sparePartSupplierId = $this->sparePartSupplier->id;
        $sparePartSupplierName = $this->sparePartSupplier->supplier_name;

        DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted spare part supplier [ID: {$sparePartSupplierId}] - Name: {$sparePartSupplierName}",
            'user_id' => auth()->id() ?? 1,
        ]);

        $this->sparePartSupplier->delete();
        session()->flash('message', 'Spare part supplier deleted successfully.');
        $this->dispatch('switchTab', tab: 'suppliers');
    }

    public function render()
    {
        return view('livewire.pages.inventory.suppliers.supplier-details');
    }
}
