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
                'title' => 'Tester calibration',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T10:00:00',
                'end' => '2026-03-25T11:00:00',
            ],
            [
                'id' => '2',
                'calendarId' => '1',
                'title' => 'Tester maintenance',
                'description' => 'Maintenance of tester description description description.',
                'type' => 'maintenance',
                'start' => '2026-03-28T09:00:00',
                'end' => '2026-03-28T12:00:00',
            ],
            [
                'id' => '3',
                'calendarId' => '1',
                'title' => 'Tester maintenance',
                'description' => 'description description description description description. description description description description description description.',
                'type' => 'maintenance',
                'start' => '2026-04-03T15:00:00',
                'end' => '2026-04-03T16:00:00',
            ],

            [
                'id' => '4',
                'calendarId' => '1',
                'title' => 'Tester calibration 01',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T10:00:00',
                'end' => '2026-03-25T11:00:00',
            ],
            [
                'id' => '5',
                'calendarId' => '1',
                'title' => 'Tester calibration 02',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T11:00:00',
                'end' => '2026-03-25T12:00:00',
            ],
            [
                'id' => '6',
                'calendarId' => '1',
                'title' => 'Tester calibration 03',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T11:00:00',
                'end' => '2026-03-25T13:00:00',
            ],
            [
                'id' => '7',
                'calendarId' => '1',
                'title' => 'Tester maintenance 02',
                'description' => 'Maintenance of tester. description description description description description.',
                'type' => 'maintenance',
                'start' => '2026-03-25T13:00:00',
                'end' => '2026-03-25T14:00:00',
            ],
            [
                'id' => '8',
                'calendarId' => '1',
                'title' => 'Tester calibration 05',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T10:00:00',
                'end' => '2026-03-25T15:00:00',
            ],
            [
                'id' => '9',
                'calendarId' => '1',
                'title' => 'Tester calibration 06',
                'description' => 'Calibration of tester. description description description description description.',
                'type' => 'calibration',
                'start' => '2026-03-25T15:00:00',
                'end' => '2026-03-25T16:00:00',
            ],
        ];

        $this->dispatch('calendar-ready');
    }

    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}