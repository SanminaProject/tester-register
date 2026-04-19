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
    public $weekOffset = 0;
    public $dateRangeDisplay = '';
    public $availableStatuses = [];

    public function mount()
    {
        $this->availableStatuses = DB::table('schedule_statuses')->pluck('name')->toArray();
        $this->fetchSchedules();
        $this->dispatch('calendar-ready');
    }

    public function previousWeek()
    {
        $this->weekOffset--;
        $this->fetchSchedules();
    }

    public function nextWeek()
    {
        $this->weekOffset++;
        $this->fetchSchedules();
    }

    public function updateEventStatus($eventId, $newStatusName)
    {
        // Event format: M-0001, C-0002, E-0003
        $type = substr($eventId, 0, 1);
        $id = (int) substr($eventId, 2);

        $statusRecord = DB::table('schedule_statuses')->where('name', strtolower($newStatusName))->first();
        if (!$statusRecord) {
            return;
        }

        if ($type === 'M') {
            DB::table('tester_maintenance_schedules')
                ->where('id', $id)
                ->update(['maintenance_status' => $statusRecord->id]);
        } elseif ($type === 'C') {
            DB::table('tester_calibration_schedules')
                ->where('id', $id)
                ->update(['calibration_status' => $statusRecord->id]);
        }
        
        // E- types (Event Logs) are historical and don't map to schedule_statuses table directly

        $this->fetchSchedules();
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


        // Date logic for weekly table filtering
        $startOfWeek = Carbon::now()->addWeeks($this->weekOffset)->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();
        
        $this->dateRangeDisplay = $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y');

        // 2. Fetch Upcoming Events for Table List (Filtered by Week)
        $mSchedules = DB::table('tester_maintenance_schedules as m')
            ->join('testers as t', 'm.tester_id', '=', 't.id')
            ->leftJoin('users as u', 'm.next_maintenance_by_user_id', '=', 'u.id')
            ->leftJoin('schedule_statuses as s', 'm.maintenance_status', '=', 's.id')
            ->selectRaw("
                CONCAT('M-', LPAD(m.id, 4, '0')) as original_id,
                m.next_maintenance_due as date,
                t.name as tester_id,
                'Maintenance' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                s.name as status
            ")
            ->whereNotNull('m.next_maintenance_due')
            ->whereBetween('m.next_maintenance_due', [$startOfWeek, $endOfWeek]);

        $cSchedules = DB::table('tester_calibration_schedules as c')
            ->join('testers as t', 'c.tester_id', '=', 't.id')
            ->leftJoin('users as u', 'c.next_calibration_by_user_id', '=', 'u.id')
            ->leftJoin('schedule_statuses as s', 'c.calibration_status', '=', 's.id')
            ->selectRaw("
                CONCAT('C-', LPAD(c.id, 4, '0')) as original_id,
                c.next_calibration_due as date,
                t.name as tester_id,
                'Calibration' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                s.name as status
            ")
            ->whereNotNull('c.next_calibration_due')
            ->whereBetween('c.next_calibration_due', [$startOfWeek, $endOfWeek]);

        $eventLogs = DB::table('tester_event_logs as e')
            ->join('testers as t', 'e.tester_id', '=', 't.id')
            ->join('event_types as et', 'e.event_type', '=', 'et.id')
            ->leftJoin('users as u', 'e.created_by_user_id', '=', 'u.id')
            ->selectRaw("
                CONCAT('E-', LPAD(e.id, 4, '0')) as original_id,
                e.date as date,
                t.name as tester_id,
                CASE WHEN et.name = 'calibration' THEN 'Calibration' ELSE 'Maintenance' END as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                'Completed' as status
            ")
            ->whereIn('et.name', ['maintenance', 'calibration'])
            ->whereBetween('e.date', [$startOfWeek, $endOfWeek]);

        $all = $mSchedules->unionAll($cSchedules)->unionAll($eventLogs)->get()->sortBy('date')->values();

        $this->upcomingEvents = $all->map(function ($event) {
            $event->id = $event->original_id;
            $event->date_formatted = Carbon::parse($event->date)->format('Y-m-d H:i');
            return (array) $event;
        })->toArray();
    }

    public function render()
    {
        return view('livewire.pages.services.service-schedule');
    }
}
