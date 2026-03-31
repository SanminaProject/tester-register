<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
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
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->waitFor('#calendar')
                ->pause(1000) 

                ->assertSee('Tester calibration')
                ->assertSee('Tester maintenance');
        });
    }
}
