<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Attributes\On;
use Livewire\Component;

class AdminPage extends Component
{
    public string $activeTab = 'personnel';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

     #[On('switchTab')]
    public function switchTab($tab = 'personnel', $id = null)
    {
        $this->activeTab = $tab['tab'] ?? 'personnel';
    }

    public function render()
    {
        return view('livewire.pages.admin.admin-page');
    }
}
