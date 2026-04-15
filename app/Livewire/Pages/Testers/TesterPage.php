<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class TesterPage extends Component
{
    public string $activeTab = 'all';
    public ?int $selectedTesterId = null;

    use WithFileUploads;

    public $tester_id, $name, $owner, $location, $status, $product_family, $type, $manufacturer, $operating_system;

    public $documents = [];

    public $search_existing_id;

    public $owners = [], $locations = [], $statuses = [], $families = [], $types = [], $manufacturers = [], $os_versions = [];

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

    public function mount()
    {
        $this->owners = ['Admin', 'Engineering', 'Production'];
        $this->locations = ['Building A', 'Building B', 'Warehouse 1'];
        $this->statuses = ['Active', 'Maintenance', 'Down'];
        $this->families = ['Series X', 'Series Y'];
        $this->types = ['In-Circuit', 'Functional'];
        $this->manufacturers = ['Agilent', 'Teradyne', 'Custom'];
        $this->os_versions = ['Windows 10', 'Windows 11', 'Linux'];
    }

    public function render()
    {
        return view('livewire.pages.testers.tester-page');
    }
}
