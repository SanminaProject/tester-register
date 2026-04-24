<?php

namespace App\Livewire\Pages\Inventory;

use Livewire\Attributes\On;
use Livewire\Component;

class InventoryPage extends Component
{
    public string $activeTab = 'spare-parts';
    public ?int $selectedSparePartId = null;
    public ?int $selectedSparePartSupplierId = null;

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
                $this->selectedSparePartSupplierId = $id;
            }

            return;
        }

        if ($tab === 'add') {
            if ($this->activeTab === 'spare-parts') {
                $tab = 'add-spare-part';
            } elseif ($this->activeTab === 'suppliers') {
                $tab = 'add-supplier';
            }
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.pages.inventory.inventory-page');
    }
}
