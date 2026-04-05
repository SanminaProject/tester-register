<?php

namespace App\Livewire;

use Livewire\Component;

class TesterPage extends Component
{
    public string $activeTab = 'all';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.tester-page');
    }
}
