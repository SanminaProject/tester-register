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

    public $search_query = '';
    public $search_results = [];

    public $owners = [], $locations = [], $statuses = [];

    public function mount()
    {
        $this->owners = ['Admin', 'Engineering'];
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
            $this->operating_system = $existing->operating_system;
            $this->type = $existing->type;
            $this->product_family = $existing->product_family;
            $this->manufacturer = $existing->manufacturer;

            $this->search_query = '';
            $this->search_results = [];
        }
    }

    public function render()
    {
        return view('livewire.pages.testers.add-new-tester');
    }
}
