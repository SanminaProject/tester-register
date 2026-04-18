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

    public function mount()
    {
        $this->testers = Tester::select('id', 'name')->get();
        $this->locations = TesterAndFixtureLocation::select('id', 'name')->get();
        $this->statuses = AssetStatus::select('id', 'name')->get();
    }

    public function save()
    {
        $this->form->save();

        $this->dispatch('saved');
        $this->dispatch('switchTab', tab: 'all');
    }
}