<?php

namespace App\Livewire\Pages\Inventory\SpareParts;

use App\Models\TesterSparePart;
use Livewire\Component;
use App\Models\User;
use App\Models\DataChangeLog;

class SparePartDetails extends Component
{
    public TesterSparePart $sparePart;

    public bool $showEmailModal = false;
    public string $selectedRecipient = '';

    public $availableRecipients = [];

    public function mount($sparePartId)
    {
        $this->sparePart = TesterSparePart::with(['tester', 'supplier'])->findOrFail($sparePartId);
        $this->availableRecipients = User::all();
    }

    public function deleteSparePart()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $sparePartId = $this->sparePart->id;
        $sparePartName = $this->sparePart->name;

        DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted spare part [ID: {$sparePartId}] - Name: {$sparePartName}",
            'spare_part_id' => $sparePartId,
            'user_id' => auth()->id() ?? 1,
        ]);

        $this->sparePart->delete();
        session()->flash('message', 'Spare part deleted successfully.');
        $this->dispatch('switchTab', tab: 'spare-parts');
    }

    public function render()
    {
        return view('livewire.pages.inventory.spare-parts.spare-part-details');
    }
}
