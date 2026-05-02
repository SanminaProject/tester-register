<?php

namespace App\Livewire\Pages\Testers;

use App\Models\TesterAsset;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use App\Models\Tester;
use App\Models\Fixture;
use App\Models\TesterCustomer;
use App\Models\TesterAndFixtureLocation;
use App\Models\AssetStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


use Illuminate\Support\Facades\Auth;
use App\Models\DataChangeLog;

class AddNewTester extends Component
{
    use WithFileUploads;

    public $tester_id;
    public $original_tester_id;
    public $isEditMode = false;
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
    public $newDocuments = [];
    public $asset_nos = [''];

    public $search_query = '';
    public $search_results = [];

    public $owners = [], $locations = [], $statuses = [];
    public $families = [], $types = [], $os_versions = [], $manufacturers = [];

    private array $documentRules = [
        'documents.*' => ['nullable', 'file', 'mimes:txt,pdf,csv,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp', 'max:10240'],
    ];

    public function mount($testerId = null)
    {
        if ($testerId) {
            $tester = Tester::with('assets')->findOrFail($testerId);
            $this->isEditMode = true;
            $this->tester_id = $tester->id;
            $this->original_tester_id = $tester->id;
            $this->name = $tester->name;
            $this->description = $tester->description;
            $this->id_number_by_customer = $tester->id_number_by_customer;
            $this->operating_system = $tester->operating_system;
            $this->type = $tester->type;
            $this->product_family = $tester->product_family;
            $this->manufacturer = $tester->manufacturer;
            $this->owner_id = $tester->owner_id;
            $this->location_id = $tester->location_id;
            $this->status_id = $tester->status;
            $this->additional_info = $tester->additional_info;
            $this->implementation_date = $tester->implementation_date instanceof \DateTimeInterface
                ? $tester->implementation_date->format('Y-m-d')
                : null;

            if ($tester->assets->count() > 0) {
                $this->asset_nos = $tester->assets->pluck('asset_no')->toArray();
            }
        } else {
            $this->isEditMode = false;
            $latest = Tester::latest('id')->first();
            $this->tester_id = ($latest->id ?? 0) + 1;
        }

        $this->owners = TesterCustomer::all();
        $this->locations = TesterAndFixtureLocation::all();
        $this->statuses = AssetStatus::all();
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

    public function createOwnerOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);

        if ($value === '') {
            return;
        }

        $owner = TesterCustomer::firstOrCreate(['name' => $value]);

        $this->owners = TesterCustomer::orderBy('name')->get();
        $this->owner_id = $owner->id;

        $this->dispatch('dropdown-option-created',
            optionId: (string) $owner->id,
            optionLabel: $owner->name,
            createMethod: 'createOwnerOption'
        );

        session()->flash('message', 'Owner added successfully.');
    }

    public function deleteOwnerOption(int $id): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $owner = TesterCustomer::find($id);
        if (! $owner) {
            return;
        }

        // Prevent deletion if any tester is using this owner
        if (Tester::where('owner_id', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteOwnerOption',
                message: 'This option is already used by tester(s), so it cannot be deleted.'
            );
            session()->flash('error', 'Option is in use and cannot be deleted.');
            return;
        }

        try {
            $owner->delete();
            $this->owners = TesterCustomer::orderBy('name')->get();

            if ($this->owner_id == $id) {
                $this->owner_id = null;
            }

            $this->dispatch('dropdown-option-deleted',
                optionId: (string) $id,
                deleteMethod: 'deleteOwnerOption'
            );

            session()->flash('message', 'Owner deleted successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteOwnerOption',
                message: 'Delete failed. Please try again.'
            );
            session()->flash('error', 'Failed to delete option.');
        }
    }

    public function createLocationOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $location = TesterAndFixtureLocation::firstOrCreate(['name' => $value]);
        $this->locations = TesterAndFixtureLocation::orderBy('name')->get();
        $this->location_id = $location->id;

        $this->dispatch('dropdown-option-created',
            optionId: (string) $location->id,
            optionLabel: $location->name,
            createMethod: 'createLocationOption'
        );
    }

    public function deleteLocationOption(int $id): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $location = TesterAndFixtureLocation::find($id);
        if (! $location) {
            return;
        }

        if (Tester::where('location_id', $id)->exists() || Fixture::where('location_id', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteLocationOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $location->delete();
        $this->locations = TesterAndFixtureLocation::orderBy('name')->get();

        if ($this->location_id == $id) {
            $this->location_id = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: (string) $id,
            deleteMethod: 'deleteLocationOption'
        );
    }

    public function createStatusOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $status = AssetStatus::firstOrCreate(['name' => $value]);
        $this->statuses = AssetStatus::orderBy('name')->get();
        $this->status_id = $status->id;

        $this->dispatch('dropdown-option-created',
            optionId: (string) $status->id,
            optionLabel: $status->name,
            createMethod: 'createStatusOption'
        );
    }

    public function deleteStatusOption(int $id): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $status = AssetStatus::find($id);
        if (! $status) {
            return;
        }

        if (Tester::where('status', $id)->exists() || Fixture::where('fixture_status', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteStatusOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $status->delete();
        $this->statuses = AssetStatus::orderBy('name')->get();

        if ($this->status_id == $id) {
            $this->status_id = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: (string) $id,
            deleteMethod: 'deleteStatusOption'
        );
    }

    public function createProductFamilyOption(string $value): void
    {
        $this->createDistinctStringOption($value, 'families', 'createProductFamilyOption');
        $this->product_family = trim($value);
    }

    public function deleteProductFamilyOption(string $value): void
    {
        $this->deleteDistinctStringOption($value, 'families', 'product_family', 'deleteProductFamilyOption');
    }

    public function createTypeOption(string $value): void
    {
        $this->createDistinctStringOption($value, 'types', 'createTypeOption');
        $this->type = trim($value);
    }

    public function deleteTypeOption(string $value): void
    {
        $this->deleteDistinctStringOption($value, 'types', 'type', 'deleteTypeOption');
    }

    public function createManufacturerOption(string $value): void
    {
        $this->createDistinctStringOption($value, 'manufacturers', 'createManufacturerOption');
        $this->manufacturer = trim($value);
    }

    public function deleteManufacturerOption(string $value): void
    {
        $this->deleteDistinctStringOption($value, 'manufacturers', 'manufacturer', 'deleteManufacturerOption');
    }

    public function createOperatingSystemOption(string $value): void
    {
        $this->createDistinctStringOption($value, 'os_versions', 'createOperatingSystemOption');
        $this->operating_system = trim($value);
    }

    public function deleteOperatingSystemOption(string $value): void
    {
        $this->deleteDistinctStringOption($value, 'os_versions', 'operating_system', 'deleteOperatingSystemOption');
    }

    private function createDistinctStringOption(string $value, string $listProperty, string $createMethod): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $list = $this->{$listProperty};
        $exists = collect($list)->contains(fn ($item) => strcasecmp((string) $item, $value) === 0);
        if (! $exists) {
            $list[] = $value;
            usort($list, fn ($a, $b) => strcasecmp((string) $a, (string) $b));
            $this->{$listProperty} = $list;
        }

        $this->dispatch('dropdown-option-created',
            optionId: $value,
            optionLabel: $value,
            createMethod: $createMethod
        );
    }

    private function deleteDistinctStringOption(string $value, string $listProperty, string $column, string $deleteMethod): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        if (Tester::where($column, $value)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: $deleteMethod,
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $this->{$listProperty} = array_values(array_filter($this->{$listProperty}, function ($item) use ($value) {
            return strcasecmp((string) $item, $value) !== 0;
        }));

        $this->dispatch('dropdown-option-deleted',
            optionId: $value,
            deleteMethod: $deleteMethod
        );
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
            $this->implementation_date = $existing->implementation_date instanceof \DateTimeInterface
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
        $idRule = $this->isEditMode 
            ? ['required', 'integer', 'unique:testers,id,' . $this->original_tester_id] 
            : ['required', 'integer', 'unique:testers,id'];

        $validated = $this->validate([
            'tester_id' => $idRule,
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
            if ($this->isEditMode) {
                $tester = Tester::with('assets')->findOrFail($this->original_tester_id);
                $original = $tester->getOriginal();
                $originalAssets = $tester->assets->pluck('asset_no')->toArray();

                $tester->update([
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

                $details = [];
                $changes = $tester->getChanges();
                unset($changes['updated_at']);

                if ($validated['tester_id'] != $this->original_tester_id) {
                    $newId = $validated['tester_id'];
                    $oldId = $this->original_tester_id;

                    // Disable foreign key checks to safely update the primary key across relations
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                    // Update referencing tables
                    DB::table('data_change_logs')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('fixtures')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('tester_assets')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('tester_calibration_schedules')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('tester_event_logs')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('tester_maintenance_schedules')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('tester_spare_parts')->where('tester_id', $oldId)->update(['tester_id' => $newId]);
                    DB::table('user_tester_assignments')->where('tester_id', $oldId)->update(['tester_id' => $newId]);

                    // Update main table
                    DB::table('testers')->where('id', $oldId)->update(['id' => $newId]);

                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                    // Move document folder if it exists
                    $oldPath = 'testers/' . $oldId . '/documents';
                    $newPath = 'testers/' . $newId . '/documents';
                    if (Storage::disk('local')->exists($oldPath)) {
                        Storage::disk('local')->move($oldPath, $newPath);
                    }

                    $tester->id = $newId;
                    $this->original_tester_id = $newId;

                    $details[] = "- id: [{$oldId}] -> [{$newId}]";
                }

                foreach ($changes as $key => $newValue) {
                    $oldValue = $original[$key] ?? null;
                    $oldStr = is_null($oldValue) || $oldValue === '' ? 'empty' : $oldValue;
                    $newStr = is_null($newValue) || $newValue === '' ? 'empty' : $newValue;
                    $details[] = "- {$key}: [{$oldStr}] -> [{$newStr}]";
                }

                $newAssets = array_values(array_filter($validated['asset_nos'] ?? [], function ($value) {
                    return trim((string) $value) !== '';
                }));

                foreach (array_values(array_diff($newAssets, $originalAssets)) as $assetNo) {
                    $details[] = "- asset_no added: [{$assetNo}]";
                }

                foreach (array_values(array_diff($originalAssets, $newAssets)) as $assetNo) {
                    $details[] = "- asset_no removed: [{$assetNo}]";
                }

                if (!empty($details)) {
                    DataChangeLog::create([
                        'changed_at' => now(),
                        'explanation' => "Edited tester details:\n" . implode("\n", $details),
                        'tester_id' => $tester->id,
                        'user_id' => Auth::id() ?? 1,
                    ]);
                }
            } else {
                $tester = new Tester([
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
                $tester->id = $validated['tester_id'];
                $tester->save();

                $details = [
                    "- id: [empty] -> [{$tester->id}]",
                    "- name: [empty] -> [{$tester->name}]",
                    "- description: [empty] -> [" . $this->formatLogValue($tester->description) . "]",
                    "- id_number_by_customer: [empty] -> [" . $this->formatLogValue($tester->id_number_by_customer) . "]",
                    "- operating_system: [empty] -> [" . $this->formatLogValue($tester->operating_system) . "]",
                    "- type: [empty] -> [" . $this->formatLogValue($tester->type) . "]",
                    "- product_family: [empty] -> [" . $this->formatLogValue($tester->product_family) . "]",
                    "- manufacturer: [empty] -> [" . $this->formatLogValue($tester->manufacturer) . "]",
                    "- implementation_date: [empty] -> [" . $this->formatLogValue($tester->implementation_date) . "]",
                    "- additional_info: [empty] -> [" . $this->formatLogValue($tester->additional_info) . "]",
                    "- location_id: [empty] -> [" . $this->formatLogValue($tester->location_id) . "]",
                    "- owner_id: [empty] -> [" . $this->formatLogValue($tester->owner_id) . "]",
                    "- status: [empty] -> [" . $this->formatLogValue($tester->status) . "]",
                ];

                $assetNos = array_values(array_filter($validated['asset_nos'] ?? [], function ($value) {
                    return trim((string) $value) !== '';
                }));

                foreach ($assetNos as $index => $assetNo) {
                    $details[] = "- asset_no " . ($index + 1) . ": [empty] -> [{$assetNo}]";
                }

                DataChangeLog::create([
                    'changed_at' => now(),
                    'explanation' => "Added new tester details:\n" . implode("\n", $details),
                    'tester_id' => $tester->id,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            if (!empty($validated['asset_nos'])) {
                // Delete old assets before inserting new ones to handle removals
                if ($this->isEditMode) {
                    $tester->assets()->delete();
                }
                foreach ($validated['asset_nos'] as $asset_no) {
                    if (!empty(trim($asset_no))) {
                        TesterAsset::create([
                            'asset_no' => trim($asset_no),
                            'tester_id' => $tester->id,
                        ]);
                    }
                }
            } else if ($this->isEditMode) {
                $tester->assets()->delete();
            }

            if (!empty($this->documents)) {
                foreach ($this->documents as $document) {
                    $originalName = $document->getClientOriginalName();
                    $document->storeAs('testers/' . $tester->id . '/documents', $originalName);
                }
            }
        });

        session()->flash('message', 'Tester saved successfully.');

        if ($this->isEditMode) {
            $this->dispatch('switchTab', ['tab' => 'details', 'id' => $this->tester_id]);
            return;
        }

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

        session()->flash('go_to_testers_last_page', true);
        $this->dispatch('switchTab', tab: 'all');
    }

    public function addAssetInput()
    {
        if (count($this->asset_nos) < 5) {
            $this->asset_nos[] = '';
        }
    }

    public function updatedNewDocuments()
    {
        $this->validate([
            'newDocuments.*' => ['nullable', 'file', 'mimes:txt,pdf,csv,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        foreach ($this->newDocuments as $doc) {
            $this->documents[] = $doc;
        }

        // Reset so the user can select another file (or the same file again) if desired
        $this->newDocuments = [];
    }

    #[Computed]
    public function existingDocuments()
    {
        if (!$this->isEditMode) {
            return collect([]);
        }

        $path = 'testers/' . $this->original_tester_id . '/documents';
        
        if (Storage::disk('local')->exists($path)) {
            return collect(Storage::disk('local')->files($path))->map(function ($filePath) {
                return [
                    'name' => basename($filePath),
                    'path' => $filePath,
                ];
            });
        }

        return collect([]);
    }

    public function deleteExistingDocument($filename)
    {
        if (!$this->isEditMode) return;
        
        $path = 'testers/' . $this->original_tester_id . '/documents/' . $filename;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            session()->flash('message', 'Document deleted successfully.');
        }
    }

    public function removeSelectedDocument($index)
    {
        if (isset($this->documents[$index])) {
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents); // Reset keys so frontend indices match correctly
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

    private function formatLogValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'empty';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
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
