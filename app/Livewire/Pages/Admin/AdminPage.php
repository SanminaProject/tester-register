<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Attributes\On;
use Livewire\Component;

class AdminPage extends Component
{
    public string $activeTab = 'personnel';
    public ?int $selectedRoleId = null;
    public ?int $selectedUserId = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('switchTab')]
    public function switchTab($tab = 'personnel', $id = null)
    {
        if ($tab === 'details') {
            if ($this->activeTab === 'roles') {
                $this->activeTab = 'role-details';
                $this->selectedRoleId = $id;
            } elseif ($this->activeTab === 'personnel') {
                $this->activeTab = 'personnel-details';
                $this->selectedUserId = $id;
            }

            return;
        }

        if ($tab === 'edit') {
            $this->activeTab = 'edit';
            $this->selectedRoleId = $id;
            return;
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.pages.admin.admin-page');
    }
}
