<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\EventLog;
use App\Livewire\Pages\Dashboard\Calendar;
use App\Models\TesterEventLog;
use App\Models\Tester;

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
        // create calibration event using factory
        $eventLog = TesterEventLog::factory()
            ->calibration()
            ->create();

        // get tester associated with the event log
        $tester = Tester::find($eventLog->tester_id);

        $eventTypeName = \DB::table('event_types')
            ->where('id', $eventLog->event_type)
            ->value('name');

        $expectedTitle = $eventTypeName . ' - ' . $tester->name;

        $this->browse(function (Browser $browser) use ($expectedTitle) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar')
                ->waitFor('.fc-event', 5)

                ->assertSeeIn('#calendar', $expectedTitle);
        });
    }
} 
