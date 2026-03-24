<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

class Calendar extends Component
{
    public $events = [];

    public function mount()
    {
        $this->events = [
            [
                'id' => '1',
                'calendarId' => '1',
                'title' => 'Team Meeting',
                'category' => 'time',
                'start' => '2026-03-25T10:00:00',
                'end' => '2026-03-25T11:00:00',
            ],
            [
                'id' => '2',
                'calendarId' => '1',
                'title' => 'Project Deadline',
                'category' => 'time',
                'start' => '2026-03-28T09:00:00',
                'end' => '2026-03-28T12:00:00',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}