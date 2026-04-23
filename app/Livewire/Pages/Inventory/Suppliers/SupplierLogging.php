<?php

namespace App\Livewire\Pages\Inventory\Suppliers;

use App\Livewire\Forms\SupplierForm;
use App\Models\TesterSparePartSupplier;
use App\Models\DataChangeLog;
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
            $supplier = TesterSparePartSupplier::findOrFail($this->sparePartSupplierId);

            if ($supplier) {
                $original = clone $supplier;
                $this->form->update();
                $supplier->refresh();
                $changes = [];

                foreach ($supplier->getAttributes() as $key => $newValue) {

                    if ($key === 'updated_at') continue;

                    $oldValue = $original->getOriginal($key);

                    if ($oldValue != $newValue) {

                        $oldStr = is_null($oldValue) ? 'empty' : $oldValue;
                        $newStr = is_null($newValue) ? 'empty' : $newValue;

                        $changes[] = "- {$key}: [{$oldStr}] -> [{$newStr}]";
                    }
                }

                if (!empty($changes)) {
                    DataChangeLog::create([
                        'changed_at' => now(),
                        'explanation' => "Edited supplier details:\n" . implode("\n", $changes),
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }
            }

            session()->flash('success', 'Supplier updated successfully!');
        } else {
            $this->form->save();

            $supplier = TesterSparePartSupplier::latest('id')->first();

            if ($supplier) {
                DataChangeLog::create([
                    'changed_at' => now(),
                    'explanation' => "Added new supplier: {$supplier->supplier_name}",
                    'user_id' => auth()->id() ?? 1,
                ]);
            }

            session()->flash('success', 'Supplier created successfully!');
        }

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'suppliers');
    }
}