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

    public function deleteFixture()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $fixtureId = $this->fixture->id;
        $fixtureName = $this->fixture->name;

        \App\Models\DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted fixture [ID: {$fixtureId}] - Name: {$fixtureName}",
            'fixture_id' => $fixtureId,
            'user_id' => auth()->id() ?? 1,
        ]);

        $this->fixture->delete();
        session()->flash('message', 'Fixture deleted successfully.');
        $this->dispatch('switchTab', tab: 'all');
    }

    public function render()
    {
        return view('livewire.pages.fixtures.fixture-details');
    }
}
