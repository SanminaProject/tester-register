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

                $this->form->update($sparePart);
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
}