<?php

namespace App\Livewire\Pages\Inventory;

use Livewire\Attributes\On;
use Livewire\Component;

class InventoryPage extends Component
{
    public string $activeTab = 'spare-parts';
    public ?int $selectedSparePartId = null;
    public ?int $selectedSupplierId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'spare-parts', $id = null)
    {
        if ($tab === 'details') {
            if ($this->activeTab === 'spare-parts') {
                $this->activeTab = 'spare-part-details';
                $this->selectedSparePartId = $id;
            } elseif ($this->activeTab === 'suppliers') {
                $this->activeTab = 'supplier-details';
                $this->selectedSupplierId = $id;
            }

            return;
        }

        if (is_array($tab)) {
            $this->activeTab = $tab['tab'] ?? 'spare-parts';
            $this->selectedSparePartId = $tab['id'] ?? null;
        } else {
            $this->activeTab = $tab ?: 'spare-parts';
            $this->selectedSparePartId = $id;
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.pages.inventory.inventory-page');
    }
}
