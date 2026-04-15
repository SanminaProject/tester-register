<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class TesterPage extends Component
{
    #[Url]
    public string $activeTab = 'all';

    #[Url(as: 'id')]
    public ?int $selectedTesterId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'all', $id = null)
    {
        if (is_array($tab)) {
            $this->activeTab = $tab['tab'] ?? 'all';
            $this->selectedTesterId = $tab['id'] ?? null;
        } else {
            $this->activeTab = $tab ?: 'all';
            $this->selectedTesterId = $id;
        }
    }

    public function render()
    {
        return view('livewire.pages.testers.tester-page');
    }
}
