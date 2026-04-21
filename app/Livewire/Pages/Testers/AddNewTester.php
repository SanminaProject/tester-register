<?php

namespace App\Livewire\Pages\Testers;

use App\Models\TesterAsset;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tester;
use Illuminate\Support\Facades\DB;


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
    public $asset_nos = [''];

    public $search_query = '';
    public $search_results = [];

    public $owners = [], $locations = [], $statuses = [];
    public $families = [], $types = [], $os_versions = [], $manufacturers = [];

    private array $documentRules = [
        'documents.*' => ['nullable', 'file', 'mimes:txt,pdf,csv,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
    ];

    public function mount()
    {
        $this->owners = \App\Models\TesterCustomer::all();
        $this->locations = \App\Models\TesterAndFixtureLocation::all();
        $this->statuses = \App\Models\AssetStatus::all();
        $this->families = $this->getDistinctTesterOptions('product_family');
        $this->types = $this->getDistinctTesterOptions('type');
        $this->manufacturers = $this->getDistinctTesterOptions('manufacturer');
        $this->os_versions = $this->getDistinctTesterOptions('operating_system');
    }

    private function getDistinctTesterOptions(string $column): array
    {
        return Tester::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->toArray();
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->search_query) < 1) {
            $this->search_results = [];
            return;
        }

        $this->search_results = Tester::where('name', 'like', '%' . $this->search_query . '%')
            ->orWhere('id_number_by_customer', 'like', '%' . $this->search_query . '%')
            ->orWhere('id', 'like', '%' . $this->search_query . '%')
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
            $this->status_id           = $existing->status;
            $this->location_id         = $existing->location_id;
            $this->type                = $existing->type;
            $this->manufacturer        = $existing->manufacturer;
            $this->operating_system    = $existing->operating_system;
            $this->implementation_date = $existing->implementation_date
                ? $existing->implementation_date->format('Y-m-d')
                : null;
            $this->additional_info     = $existing->additional_info;
            
            $existingAssets = TesterAsset::where('tester_id', $existing->id)->pluck('asset_no')->toArray();
            $this->asset_nos = !empty($existingAssets) ? array_slice($existingAssets, 0, 5) : [''];

            // Updated: keep the name in the search bar and clear results
            $this->search_query = $existing->id . ' - ' . $existing->name;
            $this->search_results = [];

            session()->flash('message', 'Data copied successfully! ');
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'id_number_by_customer' => ['nullable', 'string', 'max:50'],
            'operating_system' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'product_family' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'implementation_date' => ['nullable', 'date'],
            'additional_info' => ['nullable', 'string'],
            'location_id' => ['nullable', 'integer', 'exists:tester_and_fixture_locations,id'],
            'owner_id' => ['nullable', 'integer', 'exists:tester_customers,id'],
            'status_id' => ['nullable', 'integer', 'exists:asset_statuses,id'],
            'asset_nos' => ['nullable', 'array', 'max:5'],
            'asset_nos.*' => ['nullable', 'string', 'max:100'],
            ...$this->documentRules,
        ]);

        DB::transaction(function () use ($validated): void {
            $tester = Tester::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'id_number_by_customer' => $validated['id_number_by_customer'] ?? null,
                'operating_system' => $validated['operating_system'] ?? null,
                'type' => $validated['type'] ?? null,
                'product_family' => $validated['product_family'] ?? null,
                'manufacturer' => $validated['manufacturer'] ?? null,
                'implementation_date' => $validated['implementation_date'] ?? null,
                'additional_info' => $validated['additional_info'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'owner_id' => $validated['owner_id'] ?? null,
                'status' => $validated['status_id'] ?? null,
            ]);

            if (!empty($validated['asset_nos'])) {
                foreach ($validated['asset_nos'] as $asset_no) {
                    if (!empty(trim($asset_no))) {
                        TesterAsset::create([
                            'asset_no' => trim($asset_no),
                            'tester_id' => $tester->id,
                        ]);
                    }
                }
            }

            if (!empty($this->documents)) {
                foreach ($this->documents as $document) {
                    $document->store('testers/' . $tester->id . '/documents');
                }
            }
        });

        session()->flash('message', 'Tester saved successfully.');

        $this->reset([
            'tester_id',
            'name',
            'description',
            'id_number_by_customer',
            'operating_system',
            'type',
            'product_family',
            'manufacturer',
            'owner_id',
            'location_id',
            'status_id',
            'additional_info',
            'implementation_date',
            'documents',
        ]);
        $this->asset_nos = [''];
    }

    public function addAssetInput()
    {
        if (count($this->asset_nos) < 5) {
            $this->asset_nos[] = '';
        }
    }

    public function removeAssetInput($index)
    {
        unset($this->asset_nos[$index]);
        $this->asset_nos = array_values($this->asset_nos);
        if (empty($this->asset_nos)) {
            $this->asset_nos = [''];
        }
    }

    public function updatedDocuments(): void
    {
        $this->validate($this->documentRules);
    }

    public function render()
    {
        return view('livewire.pages.testers.add-new-tester');
    }
}
