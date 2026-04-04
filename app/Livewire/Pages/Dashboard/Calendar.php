<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\CalendarEvent;
use Livewire\Component;

class Calendar extends Component
{
    public $events = [];

    public function mount()
    {
        $this->dispatch('calendar-ready');
    }

    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}