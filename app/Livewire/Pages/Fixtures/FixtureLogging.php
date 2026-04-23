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
}