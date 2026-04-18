<?php

namespace App\Livewire\Pages\Fixtures;

use App\Livewire\Forms\FixtureForm;
use Livewire\Component;
use App\Models\Tester;
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
        $existing = \App\Models\Fixture::whereNotNull('manufacturer')
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
            $fixture = \App\Models\Fixture::find($this->fixtureId);
            if ($fixture) {
                $this->form->update($fixture);
            }
        } else {
            $this->form->save();
        }

        $this->dispatch('saved');
        session()->flash('message', $this->isEdit ? 'Fixture updated successfully.' : 'Fixture created successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }
}