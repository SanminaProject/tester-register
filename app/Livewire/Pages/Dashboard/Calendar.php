<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\CalendarEvent;
use Livewire\Component;

class Calendar extends Component
{
    public $events = [];

    public function mount()
    {
        $this->events = CalendarEvent::getCalendarEvents();

        $this->dispatch('calendar-ready', events: $this->events);
    }

    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}