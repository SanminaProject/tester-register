<?php

namespace App\Livewire\Pages\Services;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CalendarEvent;

class ServiceSchedule extends Component
{
    public $upcomingEvents = [];
    public $calendarEvents = [];

    public function mount()
    {
        $this->fetchSchedules();
        $this->dispatch('calendar-ready');
    }

    protected function fetchSchedules()
    {

        // 1. Fetch All Calendar Events using the exact same logic as Dashboard
        $this->calendarEvents = CalendarEvent::getCalendarEvents()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'type' => strtolower($event->type),
            ];
        })->toArray();


        // 2. Fetch Upcoming Events for Table List
        $mSchedules = DB::table('tester_maintenance_schedules as m')
            ->join('testers as t', 'm.tester_id', '=', 't.id')
            ->leftJoin('users as u', 'm.next_maintenance_by_user_id', '=', 'u.id')
            ->leftJoin('schedule_statuses as s', 'm.maintenance_status', '=', 's.id')
            ->selectRaw("
                m.id as original_id,
                m.next_maintenance_due as date,
                t.name as tester_id,
                'Maintenance' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                s.name as status
            ")
            ->whereNotNull('m.next_maintenance_due')
            ->get();

        $cSchedules = DB::table('tester_calibration_schedules as c')
            ->join('testers as t', 'c.tester_id', '=', 't.id')
            ->leftJoin('users as u', 'c.next_calibration_by_user_id', '=', 'u.id')
            ->leftJoin('schedule_statuses as s', 'c.calibration_status', '=', 's.id')
            ->selectRaw("
                c.id as original_id,
                c.next_calibration_due as date,
                t.name as tester_id,
                'Calibration' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                s.name as status
            ")
            ->whereNotNull('c.next_calibration_due')
            ->get();

        $all = $mSchedules->merge($cSchedules)->sortBy('date')->values();

        $this->upcomingEvents = $all->map(function ($event) {
            $event->id = ($event->maintenance_calibration === 'Maintenance' ? 'M-' : 'C-') . str_pad($event->original_id, 4, '0', STR_PAD_LEFT);
            $event->date_formatted = Carbon::parse($event->date)->format('Y-m-d H:i');
            return (array) $event;
        })->toArray();
    }

    public function render()
    {
        return view('livewire.pages.services.service-schedule');
    }
}
