<?php

namespace App\Livewire\Pages\Fixtures;

use App\Models\Fixture;
use Livewire\Component;

class FixtureDetails extends Component
{
    public Fixture $fixture;

    public function mount($fixtureId)
    {
        $this->fixture = Fixture::with(['tester', 'location', 'status'])->findOrFail($fixtureId);
    }

    public function render()
    {
        return view('livewire.pages.fixtures.fixture-details');
    }
}
