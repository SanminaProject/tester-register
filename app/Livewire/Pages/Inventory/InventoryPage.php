<?php

namespace App\Livewire\Pages\Inventory;

use Livewire\Attributes\On;
use Livewire\Component;

class InventoryPage extends Component
{
    public string $activeTab = 'spare-parts';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'spare-parts', $id = null)
    {
        if (is_array($tab)) {
            $this->activeTab = $tab['tab'] ?? 'spare-parts';
            $this->selectedFixtureId = $tab['id'] ?? null;
        } else {
            $this->activeTab = $tab ?: 'spare-parts';
            $this->selectedFixtureId = $id;
        }
    }

    public function render()
    {
        return view('livewire.pages.inventory.inventory-page');
    }
}
