<?php

namespace App\Livewire\Pages\Fixtures;

use App\Livewire\Forms\FixtureForm;
use Livewire\Component;
use App\Models\Tester;
use App\Models\Fixture;
use App\Models\DataChangeLog;
use App\Models\TesterAndFixtureLocation;
use App\Models\AssetStatus;

class FixtureLogging extends Component
{
    public FixtureForm $form;

    public $testers = [];
    public $locations = [];
    public $statuses = [];
    public $manufacturers = [];

    public ?int $fixtureId = null;
    public bool $isEdit = false;

    public function mount($fixtureId = null)
    {
        if ($fixtureId) {
            $this->fixtureId = $fixtureId;
            $this->isEdit = true;
            $fixture = \App\Models\Fixture::find($fixtureId);
            if ($fixture) {
                $this->form->setFixture($fixture);
            }
        }

        $this->testers = Tester::select('id', 'name')->get();
        $this->locations = TesterAndFixtureLocation::select('id', 'name')->get();
        $this->statuses = AssetStatus::select('id', 'name')->get();

        // Get distinct manufacturers from existing fixtures and add some common defaults
        $existing = Fixture::whereNotNull('manufacturer')
            ->distinct()
            ->pluck('manufacturer')
            ->toArray();
            
        $defaults = ['Agilent', 'Teradyne', 'Custom'];
        $allManufacturers = array_unique(array_merge($defaults, $existing));
        sort($allManufacturers);

        $this->manufacturers = array_map(function ($m) {
            return ['id' => $m, 'name' => $m];
        }, $allManufacturers);
    }

    public function save()
    {
        if ($this->isEdit && $this->fixtureId) {
            $fixture = Fixture::find($this->fixtureId);
            if ($fixture) {
                $original = clone $fixture;
                $this->form->update($fixture);

                $changes = $fixture->getChanges();
                if (count($changes) > 0) {
                    // Exclude 'updated_at' from the list of changed fields if it exists
                    unset($changes['updated_at']);
                    
                    if (count($changes) > 0) {
                        $details = [];
                        foreach ($changes as $key => $newValue) {
                            $oldValue = $original->getOriginal($key);
                            $oldStr = is_null($oldValue) ? 'empty' : $oldValue;
                            $newStr = is_null($newValue) ? 'empty' : $newValue;
                            $details[] = "- {$key}: [{$oldStr}] -> [{$newStr}]";
                        }
                        
                        $explanation = "Edited fixture details:\n" . implode("\n", $details);
                        
                        DataChangeLog::create([
                            'changed_at' => now(),
                            'explanation' => $explanation,
                            'fixture_id' => $fixture->id,
                            'user_id' => auth()->id() ?? 1,
                        ]);
                    }
                }
            }
        } else {
            $this->form->save();
            
            $fixture = Fixture::latest('id')->first();
            if ($fixture) {
                DataChangeLog::create([
                    'changed_at' => now(),
                    'explanation' => "Added new fixture: {$fixture->name}",
                    'fixture_id' => $fixture->id,
                    'user_id' => auth()->id() ?? 1,
                ]);
            }
        }

        $this->dispatch('saved');
        session()->flash('message', $this->isEdit ? 'Fixture updated successfully.' : 'Fixture created successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function createManufacturerOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        $existing = collect($this->manufacturers)->contains(function ($item) use ($value) {
            $name = is_array($item) ? ($item['name'] ?? '') : '';
            return strcasecmp($name, $value) === 0;
        });

        if (! $existing) {
            $this->manufacturers[] = ['id' => $value, 'name' => $value];
        }

        usort($this->manufacturers, function ($a, $b) {
            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        $this->form->manufacturer = $value;

        $this->dispatch('dropdown-option-created',
            optionId: $value,
            optionLabel: $value,
            createMethod: 'createManufacturerOption'
        );
    }

    public function deleteManufacturerOption(string $value): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('Admin')) {
            return;
        }

        $value = trim($value);
        if ($value === '') {
            return;
        }

        if (Fixture::where('manufacturer', $value)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteManufacturerOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $this->manufacturers = array_values(array_filter($this->manufacturers, function ($item) use ($value) {
            $name = is_array($item) ? ($item['name'] ?? '') : '';
            return strcasecmp((string) $name, $value) !== 0;
        }));

        if ((string) ($this->form->manufacturer ?? '') === $value) {
            $this->form->manufacturer = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: $value,
            deleteMethod: 'deleteManufacturerOption'
        );
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
        $this->locations = TesterAndFixtureLocation::select('id', 'name')->orderBy('name')->get();
        $this->form->location_id = $location->id;

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

        if (Fixture::where('location_id', $id)->exists() || Tester::where('location_id', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteLocationOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $location->delete();
        $this->locations = TesterAndFixtureLocation::select('id', 'name')->orderBy('name')->get();

        if ((int) ($this->form->location_id ?? 0) === $id) {
            $this->form->location_id = null;
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
        $this->statuses = AssetStatus::select('id', 'name')->orderBy('name')->get();
        $this->form->fixture_status = $status->id;

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

        if (Fixture::where('fixture_status', $id)->exists() || Tester::where('status', $id)->exists()) {
            $this->dispatch('dropdown-option-delete-failed',
                deleteMethod: 'deleteStatusOption',
                message: 'This option is already in use and cannot be deleted.'
            );
            return;
        }

        $status->delete();
        $this->statuses = AssetStatus::select('id', 'name')->orderBy('name')->get();

        if ((int) ($this->form->fixture_status ?? 0) === $id) {
            $this->form->fixture_status = null;
        }

        $this->dispatch('dropdown-option-deleted',
            optionId: (string) $id,
            deleteMethod: 'deleteStatusOption'
        );
    }
}