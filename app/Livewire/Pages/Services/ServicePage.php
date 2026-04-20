<?php

namespace App\Livewire\Pages\Services;

use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Component;

class ServicePage extends Component
{
    #[Url]
    public string $activeTab = 'schedules';

    #[Url(as: 'tester_id')]
    public ?int $testerId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'schedules')
    {
        $this->activeTab = is_array($tab) ? ($tab['tab'] ?? 'schedules') : ($tab ?: 'schedules');
    }

    public function render()
    {
        return view('livewire.pages.services.service-page');
    }
}
