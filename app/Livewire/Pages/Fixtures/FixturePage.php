<?php

namespace App\Livewire\Pages\Fixtures;

use Livewire\Attributes\On;
use Livewire\Component;

class FixturePage extends Component
{
    public string $activeTab = 'all';
    public ?int $selectedFixtureId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'all', $id = null)
    {
        if (is_array($tab)) {
            $this->activeTab = $tab['tab'] ?? 'all';
            $this->selectedFixtureId = $tab['id'] ?? null;
        } else {
            $this->activeTab = $tab ?: 'all';
            $this->selectedFixtureId = $id;
        }
    }

    public function render()
    {
        return view('livewire.pages.fixtures.fixture-page');
    }
}
