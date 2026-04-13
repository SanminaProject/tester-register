<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\Fixture;

class FixtureForm extends Form
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('nullable|string|max:100')]
    public ?string $manufacturer = null;

    #[Validate('required|exists:testers,id')]
    public ?int $tester_id = null;

    #[Validate('nullable|exists:tester_and_fixture_locations,id')]
    public ?int $location_id = null;

    #[Validate('nullable|exists:asset_statuses,id')]
    public ?int $fixture_status = null;

    /**
     * Create a new fixture
     */
    public function save()
    {
        $this->validate();

        Fixture::create([
            'name' => $this->name,
            'description' => $this->description,
            'manufacturer' => $this->manufacturer,
            'tester_id' => $this->tester_id,
            'location_id' => $this->location_id,
            'fixture_status' => $this->fixture_status,
        ]);
    }

    /**
     * Fill form for editing
     */
    public function setFixture(Fixture $fixture)
    {
        $this->fill($fixture->only([
            'name',
            'description',
            'manufacturer',
            'tester_id',
            'location_id',
            'fixture_status',
        ]));
    }

    /**
     * Update existing fixture
     */
    public function update(Fixture $fixture)
    {
        $this->validate();

        $fixture->update([
            'name' => $this->name,
            'description' => $this->description,
            'manufacturer' => $this->manufacturer,
            'tester_id' => $this->tester_id,
            'location_id' => $this->location_id,
            'fixture_status' => $this->fixture_status,
        ]);
    }
}