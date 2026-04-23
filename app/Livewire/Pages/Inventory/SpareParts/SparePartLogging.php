<?php

namespace App\Livewire\Pages\Inventory\SpareParts;

use App\Livewire\Forms\SparePartForm;
use App\Models\TesterSparePart;
use App\Models\Tester;
use App\Models\TesterSparePartSupplier;
use App\Models\DataChangeLog;
use Livewire\Component;

class SparePartLogging extends Component
{
    public SparePartForm $form;

    public ?int $sparePartId = null;
    public bool $isEdit = false;

    public function render()
    {
        return view('livewire.pages.inventory.spare-parts.spare-part-logging', [
            'testers' => Tester::select('name', 'id')->get(),
            'suppliers' => TesterSparePartSupplier::select('supplier_name as name', 'id')->get(),
        ]);
    }

    public function mount($sparePartId = null)
    {
        if ($sparePartId) {
            $this->sparePartId = $sparePartId;
            $this->isEdit = true;

            $sparePart = TesterSparePart::findOrFail($sparePartId);

            // fill form with existing data
            $this->form->setSparePart($sparePart);
        }
    }

    public function save()
    {
        if ($this->isEdit) {
            $sparePart = TesterSparePart::findOrFail($this->sparePartId);

            $this->form->update($sparePart);

            DataChangeLog::create([
                'changed_at' => now(),
                'explanation' => "Updated spare part [ID: {$sparePart->id}] - Name: {$sparePart->name}",
                'spare_part_id' => $sparePart->id,
                'user_id' => auth()->id() ?? 1,
            ]);

            session()->flash('success', 'Spare part updated successfully!');
        } else {
            $this->form->save();

            session()->flash('success', 'Spare part created successfully!');
        }

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'spare-parts');
    }
}