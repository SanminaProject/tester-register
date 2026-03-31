<?php

namespace Tests\Feature\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Livewire\Pages\Dashboard\Calendar;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;


class CalendarTest extends TestCase
{
    use RefreshDatabase;
    protected User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalUser = User::factory()->create();
    }

    // -- Display tests --
    public function calendar_initializes(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertStatus(200)
            ->assertSee('calendar'); // div id
    }

    public function calendar_displays_current_month_by_default(): void
    {
        $this->actingAs($this->normalUser);

        $now = now()->format('F Y'); 

        Livewire::test(Calendar::class)
            ->assertSee($now);
    }

    public function calendar_displays_header_toolbar(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertSee('today')
            ->assertSee('prev')
            ->assertSee('next');
    }

    public function calendar_shows_events_from_backend(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertSee('Tester calibration')
            ->assertSee('Tester maintenance');
    }

    public function events_are_set_correctly(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertSet('events', function ($events) {
                return count($events) === 9;
            });
    }

    public function events_have_correct_structure(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertSet('events', function ($events) {
                return isset($events[0]['title']) &&
                       isset($events[0]['start']) &&
                       isset($events[0]['end']);
            });
    }

    public function calendar_handles_no_events(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->set('events', [])
            ->assertSet('events', [])
            ->assertSee('calendar');
    }

    public function multiple_events_exist_on_same_day(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertSet('events', function ($events) {
                $sameDayEvents = array_filter($events, function ($event) {
                    return str_contains($event['start'], '2026-03-25');
                });

                return count($sameDayEvents) > 1;
            });
    }

    public function calendar_ready_event_is_dispatched(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(Calendar::class)
            ->assertDispatched('calendar-ready');
    }
}



