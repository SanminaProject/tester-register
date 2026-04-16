<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tester;

/**
 * This Livewire component manages the tester page, allowing users to view, search, and copy tester information.
 * It includes functionality for switching between tabs, searching testers, and copying existing tester data into a form.
 */
class TesterPage extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'all';

    #[Url(as: 'tester')]
    public ?int $selectedTesterId = null;

    use WithFileUploads;

    public $tester_id, $name, $owner, $location, $status, $product_family, $type, $manufacturer, $operating_system;

    public $documents = [];

    public $search_query = '';
    public $search_results = [];

    public $owners = [], $locations = [], $statuses = [], $families = [], $types = [], $manufacturers = [], $os_versions = [];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Listen for the 'switchTab' event to update the active tab and selected tester ID
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

    // Initialize dropdown options for the form
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

    // Update search results when the search query changes
    public function updatedSearchQuery()
    {
        if (strlen($this->search_query) < 2) {
            $this->search_results = [];
            return;
        }

        $this->search_results = Tester::where('name', 'like', '%' . $this->search_query . '%')
            ->orWhere('id_number_by_customer', 'like', '%' . $this->search_query . '%')
            ->orWhere('description', 'like', '%' . $this->search_query . '%')
            ->limit(5)
            ->get(['id', 'name', 'id_number_by_customer'])
            ->toArray();
    }
    // Copy data from an existing tester to the form fields
    public function selectAndCopyTester($id)
    {
        $existingTester = Tester::find($id);

        if ($existingTester) {
            $this->owner            = $existingTester->owner_id;
            $this->location         = $existingTester->location_id;
            $this->status           = $existingTester->status;
            $this->product_family   = $existingTester->product_family;
            $this->type             = $existingTester->type;
            $this->manufacturer     = $existingTester->manufacturer;
            $this->operating_system = $existingTester->operating_system;

            $this->search_query = '';
            $this->search_results = [];

            session()->flash('message', 'Data filled from: ' . $existingTester->name);
        }
    }
    // Render the Livewire component view
    public function render()
    {
        return view('livewire.pages.testers.tester-page');
    }
}
