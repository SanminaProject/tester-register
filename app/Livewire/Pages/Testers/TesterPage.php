<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Attributes\On;
use Livewire\Component;

class TesterPage extends Component
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
        return view('livewire.pages.testers.tester-page');
    }
}
