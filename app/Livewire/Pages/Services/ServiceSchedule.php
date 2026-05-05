<?php

namespace App\Livewire\Pages\Services;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CalendarEvent;
use App\Models\TesterMaintenanceSchedule;
use App\Models\TesterCalibrationSchedule;
use App\Models\TesterEventLog as EventLog;

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

        // If user marked completed, perform the completion actions: record last date, create event log, compute next due
        if (strtolower($newStatusName) === 'completed') {
            $completedAt = Carbon::now()->endOfDay();
            $actorUserId = auth()->id() ?: null;

            if ($type === 'M') {
                $schedule = TesterMaintenanceSchedule::find($id);
                if ($schedule) {
                    // compute next due
                    $procedure = DB::table('tester_maintenance_procedures as p')
                        ->join('procedure_interval_units as u', 'p.interval_unit', '=', 'u.id')
                        ->where('p.id', $schedule->maintenance_id)
                        ->select('p.interval_value', 'u.name as unit')
                        ->first();

                    $nextDue = null;
                    if ($procedure) {
                        $next = $completedAt->copy();
                        $intervalValue = max(1, (int) $procedure->interval_value);
                        $unit = strtolower((string) $procedure->unit);
                        if ($unit === 'days') $next->addDays($intervalValue);
                        elseif ($unit === 'weeks') $next->addWeeks($intervalValue);
                        elseif ($unit === 'months') $next->addMonths($intervalValue);
                        elseif ($unit === 'years') $next->addYears($intervalValue);
                        $nextDue = $next;
                    }

                    $update = [
                        'last_maintenance_date' => $completedAt,
                        'last_maintenance_by_user_id' => $actorUserId,
                    ];

                    if ($nextDue !== null) {
                        $update['next_maintenance_due'] = $nextDue;
                        $scheduledStatusId = DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['scheduled'])->value('id');
                        if ($scheduledStatusId) $update['maintenance_status'] = $scheduledStatusId;
                    } else {
                        $completedStatusId = DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['completed'])->value('id');
                        if ($completedStatusId) $update['maintenance_status'] = $completedStatusId;
                    }

                    $schedule->update($update);

                    // create event log
                    $eventTypeId = DB::table('event_types')->whereRaw('LOWER(name) = ?', ['maintenance'])->value('id');
                    if ($eventTypeId) {
                        EventLog::create([
                            'tester_id' => $schedule->tester_id,
                            'event_type' => $eventTypeId,
                            'date' => $completedAt,
                            'description' => sprintf('Maintenance completed: %s', DB::table('tester_maintenance_procedures')->where('id', $schedule->maintenance_id)->value('type') ?? 'Maintenance'),
                            'created_by_user_id' => $actorUserId,
                            'maintenance_schedule_id' => $schedule->id,
                            'calibration_schedule_id' => null,
                            'resolution_description' => null,
                            'resolved_date' => $completedAt,
                            'resolved_by_user_id' => $actorUserId,
                        ]);
                    }

                    // dispatch a browser event so front-end can re-emit to Livewire if needed
                    $this->dispatch('tester-updated', testerId: $schedule->tester_id);
                }
            } elseif ($type === 'C') {
                $schedule = TesterCalibrationSchedule::find($id);
                if ($schedule) {
                    // compute next due
                    $procedure = DB::table('tester_calibration_procedures as p')
                        ->join('procedure_interval_units as u', 'p.interval_unit', '=', 'u.id')
                        ->where('p.id', $schedule->calibration_id)
                        ->select('p.interval_value', 'u.name as unit')
                        ->first();

                    $nextDue = null;
                    if ($procedure) {
                        $next = $completedAt->copy();
                        $intervalValue = max(1, (int) $procedure->interval_value);
                        $unit = strtolower((string) $procedure->unit);
                        if ($unit === 'days') $next->addDays($intervalValue);
                        elseif ($unit === 'weeks') $next->addWeeks($intervalValue);
                        elseif ($unit === 'months') $next->addMonths($intervalValue);
                        elseif ($unit === 'years') $next->addYears($intervalValue);
                        $nextDue = $next;
                    }

                    $update = [
                        'last_calibration_date' => $completedAt,
                        'last_calibration_by_user_id' => $actorUserId,
                    ];

                    if ($nextDue !== null) {
                        $update['next_calibration_due'] = $nextDue;
                        $scheduledStatusId = DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['scheduled'])->value('id');
                        if ($scheduledStatusId) $update['calibration_status'] = $scheduledStatusId;
                    } else {
                        $completedStatusId = DB::table('schedule_statuses')->whereRaw('LOWER(name) = ?', ['completed'])->value('id');
                        if ($completedStatusId) $update['calibration_status'] = $completedStatusId;
                    }

                    $schedule->update($update);

                    // create event log
                    $eventTypeId = DB::table('event_types')->whereRaw('LOWER(name) = ?', ['calibration'])->value('id');
                    if ($eventTypeId) {
                        EventLog::create([
                            'tester_id' => $schedule->tester_id,
                            'event_type' => $eventTypeId,
                            'date' => $completedAt,
                            'description' => sprintf('Calibration completed: %s', DB::table('tester_calibration_procedures')->where('id', $schedule->calibration_id)->value('type') ?? 'Calibration'),
                            'created_by_user_id' => $actorUserId,
                            'maintenance_schedule_id' => null,
                            'calibration_schedule_id' => $schedule->id,
                            'resolution_description' => null,
                            'resolved_date' => $completedAt,
                            'resolved_by_user_id' => $actorUserId,
                        ]);
                    }

                    // dispatch a browser event so front-end can re-emit to Livewire if needed
                    $this->dispatch('tester-updated', testerId: $schedule->tester_id);
                }
            }
        }

        $this->fetchSchedules();

        // push updated events to the browser calendar
        $this->dispatch('calendar-update', events: $this->calendarEvents);
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
                'allDay' => $event->allDay ?? true,
                'type' => strtolower($event->type),
                'event_code' => $event->event_code ?? $event->id,
                'tester_id' => $event->tester_id,
                'tester_name' => $event->tester_name,
                'maintenance_calibration' => $event->maintenance_calibration,
                'user_name' => $event->user_name,
                'event_status' => $event->event_status,
                'last_date' => $event->last_date ?? null,
                'next_date' => $event->next_date ?? null,
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
                t.id as tester_id,
                t.name as tester_name,
                'Maintenance' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                CASE
                    WHEN LOWER(s.name) = 'completed' THEN 'completed'
                    WHEN m.next_maintenance_due IS NOT NULL AND DATE(NOW()) > DATE(m.next_maintenance_due) THEN 'overdue'
                    ELSE 'scheduled'
                END as status
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
                t.id as tester_id,
                t.name as tester_name,
                'Calibration' as maintenance_calibration,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                CASE
                    WHEN LOWER(s.name) = 'completed' THEN 'completed'
                    WHEN c.next_calibration_due IS NOT NULL AND DATE(NOW()) > DATE(c.next_calibration_due) THEN 'overdue'
                    ELSE 'scheduled'
                END as status
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
                t.id as tester_id,
                t.name as tester_name,
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
