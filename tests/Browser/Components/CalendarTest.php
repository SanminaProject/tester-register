<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\EventLog;
use App\Livewire\Pages\Dashboard\Calendar;

class CalendarTest extends DuskTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_calendar_is_visible()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar') 
                ->assertVisible('#calendar');
        });
    }

    public function test_events_are_visible_on_calendar()
    {
        $event = EventLog::factory()->create([
            'title' => 'Dusk Test Event',
            'type' => 'calibration',
            'start' => '2026-03-25T10:00:00',
            'end' => '2026-03-25T11:00:00',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar')
                ->waitFor('.fc-event', 5)

                ->assertSeeIn('#calendar', 'Dusk Test Event');
        });
    }
} 
