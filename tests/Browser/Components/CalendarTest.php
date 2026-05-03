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
} 
