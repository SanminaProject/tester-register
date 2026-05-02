<?php

namespace App\Livewire\Pages\Inventory\SpareParts;

use App\Livewire\Forms\SparePartForm;
use App\Models\TesterSparePart;
use App\Models\Tester;
use App\Models\User;
use App\Models\TesterSparePartSupplier;
use App\Models\DataChangeLog;
use Livewire\Component;
use Illuminate\Support\Carbon;

class SparePartLogging extends Component
{
    public SparePartForm $form;

    public ?int $sparePartId = null;
    public bool $isEdit = false;

    #[\Livewire\Attributes\On('recipientsUpdated')]
    public function updateResponsibleUsers($selectedIds)
    {
        $this->form->responsible_user_ids = $selectedIds;
    }

    public function render()
    {
        return view('livewire.pages.inventory.spare-parts.spare-part-logging', [
            'testers' => Tester::select('name', 'id')->get(),
            'suppliers' => TesterSparePartSupplier::select('supplier_name as name', 'id')->get(),
            'users' => User::orderBy('first_name')->get(),
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

            if ($sparePart) {
                $original = clone $sparePart;

                $originalResponsibleUsers = $sparePart->responsibleUsers
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();

                $this->form->update();
                $sparePart->refresh();

                $changes = [];

                foreach ($sparePart->getAttributes() as $key => $newValue) {
                    if ($key === 'updated_at') continue;

                    $oldValue = $original->getOriginal($key);

                    if ($key === 'last_order_date') {
                        $oldValue = $oldValue ? Carbon::parse($oldValue)->format('Y-m-d') : null;
                        $newValue = $newValue ? Carbon::parse($newValue)->format('Y-m-d') : null;
                    }

                    if ($oldValue != $newValue) {
                        $oldStr = is_null($oldValue) ? 'empty' : $oldValue;
                        $newStr = is_null($newValue) ? 'empty' : $newValue;
                        $changes[] = "- {$key}: [{$oldStr}] -> [{$newStr}]";
                    }
                }

                $newResponsibleUsers = $sparePart->responsibleUsers
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();

                if ($originalResponsibleUsers !== $newResponsibleUsers) {
                    $oldNames = User::whereIn('id', $originalResponsibleUsers)
                        ->get()
                        ->map(fn ($u) => "{$u->first_name} {$u->last_name}")
                        ->join(', ');

                    $newNames = User::whereIn('id', $newResponsibleUsers)
                        ->get()
                        ->map(fn ($u) => "{$u->first_name} {$u->last_name}")
                        ->join(', ');

                    $changes[] = "- responsible_users: [{$oldNames}] -> [{$newNames}]";
                }

                if (!empty($changes)) {
                    DataChangeLog::create([
                        'changed_at' => now(),
                        'explanation' => "Edited spare part details:\n" . implode("\n", $changes),
                        'spare_part_id' => $sparePart->id,
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }
            }

            session()->flash('success', 'Spare part updated successfully!');
        } else {
            $this->form->save();

            $sparePart = TesterSparePart::latest('id')->first();
            if ($sparePart) {
                DataChangeLog::create([
                    'changed_at' => now(),
                    'explanation' => "Added new spare part: {$sparePart->name}",
                    'spare_part_id' => $sparePart->id,
                    'user_id' => auth()->id() ?? 1,
                ]);
            }

            session()->flash('success', 'Spare part created successfully!');
        }

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'spare-parts');
    }

    public function createSupplierOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $supplier = TesterSparePartSupplier::firstOrCreate(['supplier_name' => $value]);
        $this->form->supplier_id = $supplier->id;

        $this->dispatch('dropdown-option-created',
            optionId: (string) $supplier->id,
            optionLabel: $supplier->supplier_name,
            createMethod: 'createSupplierOption'
        );
    }

    public function deleteSupplierOption(int $id): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $supplier = TesterSparePartSupplier::find($id);
        if (! $supplier) {
            return;
        }

        if (TesterSparePart::where('supplier_id', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteSupplierOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $supplier->delete();

        if ((int) ($this->form->supplier_id ?? 0) === $id) {
            $this->form->supplier_id = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: (string) $id,
            deleteMethod: 'deleteSupplierOption'
        );
    }
}