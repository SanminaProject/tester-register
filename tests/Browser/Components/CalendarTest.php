<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TesterEventLog;

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

    public function test_calendar_displays_current_month_by_default() {
        $currentMonthYear = now()->format('F Y'); 

        $this->browse(function (Browser $browser) use ($currentMonthYear) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar') 
                ->assertSeeIn('.fc-toolbar .fc-toolbar-chunk:nth-child(2)', $currentMonthYear);
        });
    }

    public function test_events_are_visible_on_calendar()
    {
        $eventLog = TesterEventLog::factory()
            ->calibration()
            ->create();
       
        $tester = $eventLog->tester; 
        $eventType = $eventLog->eventType; 

        $expectedTitle = $eventType->name . ' - ' . $tester->name; 

        $this->browse(function (Browser $browser) use ($expectedTitle) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar')
                ->pause(3000);

            $texts = $browser->script("
                return Array.from(document.querySelectorAll('.fc-event-title'))
                    .map(el => el.innerText);
            ");

            dump($texts); // all backend events are shown properly

            // this will give an error, the event title can't be found properly
            $browser->waitFor('.fc-event-title', 5)
                ->assertPresent("//div[contains(@class, 'fc-event-title') and contains(., '{$expectedTitle}')]");
        });
    }
} 
