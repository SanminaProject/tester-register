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

        $linkedSparePartsCount = $this->sparePartSupplier->spareParts()->count();

        if ($linkedSparePartsCount > 0) {
            session()->flash('error', "This supplier cannot be deleted because it is associated with {$linkedSparePartsCount} spare part(s). Please remove or reassign those spare parts first.");
            return;
        }

        $sparePartSupplierId = $this->sparePartSupplier->id;
        $sparePartSupplierName = $this->sparePartSupplier->supplier_name;

        DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted supplier [ID: {$sparePartSupplierId}] - Name: {$sparePartSupplierName}",
            'spare_part_supplier_id' => $sparePartSupplierId,
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
