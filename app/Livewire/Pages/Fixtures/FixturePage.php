<?php

namespace App\Livewire\Pages\Fixtures;

use Livewire\Component;

class FixturePage extends Component
{
    public string $activeTab = 'all';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.pages.fixtures.fixture-page');
    }
}
