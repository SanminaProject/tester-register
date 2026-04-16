<?php

namespace App\Livewire\Pages\Testers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tester;

class AddNewTester extends Component
{
    use WithFileUploads;

    public $tester_id;
    public $name;
    public $description;
    public $id_number_by_customer;
    public $operating_system;
    public $type;
    public $product_family;
    public $manufacturer;
    public $owner_id;
    public $location_id;
    public $status_id;
    public $additional_info;
    public $implementation_date;
    public $documents = [];

    public $search_query = '';
    public $search_results = [];

    public $owners = [], $locations = [], $statuses = [];
    public $families = [], $os_versions = [], $manufacturers = [];

    public function mount()
    {
        $this->owners = \App\Models\TesterCustomer::all();
        $this->locations = \App\Models\TesterAndFixtureLocation::all();
        $this->statuses = \App\Models\AssetStatus::all();
        // These could be dynamic in a real application, but for now we'll hardcode some options
        $this->families = ['Series X', 'Series Y', 'Z-Platform'];
        $this->manufacturers = ['Agilent', 'Teradyne', 'Custom'];
        $this->os_versions = ['Windows 10', 'Windows 11', 'Linux'];
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->search_query) < 2) {
            $this->search_results = [];
            return;
        }

        $this->search_results = Tester::where('name', 'like', '%' . $this->search_query . '%')
            ->orWhere('id_number_by_customer', 'like', '%' . $this->search_query . '%')
            ->limit(5)
            ->get(['id', 'name', 'id_number_by_customer'])
            ->toArray();
    }

    public function selectAndCopyTester($id)
    {
        $existing = Tester::find($id);
        if ($existing) {
            // Copy relevant fields from the existing tester to the form fields
            $this->description         = $existing->description;
            $this->id_number_by_customer = $existing->id_number_by_customer;
            $this->product_family      = $existing->product_family;
            $this->owner_id            = $existing->owner_id;
            $this->status_id           = $existing->status; // Matching migration field
            $this->location_id         = $existing->location_id;
            $this->type                = $existing->type;
            $this->manufacturer        = $existing->manufacturer;
            $this->operating_system    = $existing->operating_system;
            $this->implementation_date = $existing->implementation_date;
            $this->additional_info     = $existing->additional_info;

            // Clear search state
            $this->search_query = '';
            $this->search_results = [];

            session()->flash('message', 'Data copied successfully! ');
        }
    }

    public function render()
    {
        return view('livewire.pages.testers.add-new-tester');
    }
}
