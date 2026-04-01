<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Event;
use Livewire\Component;

class Calendar extends Component
{
    public $events = [];

    public function mount()
    {
        $this->events = Event::all()->map(function ($event) {
            return [
                'id' => $event->id,
                'calendarId' => 1,
                'title' => $event->title,
                'type' => $event->type,
                'start' => $event->start->toIso8601String(),
                'end' => $event->end->toIso8601String(),
            ];
        })->toArray();

        $this->dispatch('calendar-ready');
    }

    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}