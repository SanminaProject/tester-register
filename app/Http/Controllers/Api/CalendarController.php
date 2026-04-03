<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\TesterMaintenanceSchedule;
use App\Models\TesterCalibrationSchedule;
use App\Models\TesterEventLog;

class CalendarController extends Controller
{
    /**
     * Return all calendar events.
     */
    public function index()
    {
        // Maintenance (planned)
        $maintenanceEvents = DB::table('tester_maintenance_schedules')
            ->join('testers', 'tester_maintenance_schedules.tester_id', '=', 'testers.id')
            ->selectRaw("
                CONCAT('maintenance-', tester_maintenance_schedules.id) as id,
                testers.id as tester_id,
                CONCAT('Maintenance - ', testers.name) as title,
                'maintenance' as type,
                next_maintenance_due as start,
                DATE_ADD(next_maintenance_due, INTERVAL 1 HOUR) as end
            ");

        // Calibration (planned)
        $calibrationEvents = DB::table('tester_calibration_schedules')
            ->join('testers', 'tester_calibration_schedules.tester_id', '=', 'testers.id')
            ->selectRaw("
                CONCAT('calibration-', tester_calibration_schedules.id) as id,
                testers.id as tester_id,
                CONCAT('Calibration - ', testers.name) as title,
                'calibration' as type,
                next_calibration_due as start,
                DATE_ADD(next_calibration_due, INTERVAL 1 HOUR) as end
            ");

        // Event logs (past and ongoing)
        $eventLogs = DB::table('tester_event_logs')
            ->join('event_types', 'tester_event_logs.event_type', '=', 'event_types.id')
            ->join('testers', 'tester_event_logs.tester_id', '=', 'testers.id')
            ->selectRaw("
                CONCAT('event-', tester_event_logs.id) as id,
                testers.id as tester_id,
                CONCAT(event_types.name, ' - ', testers.name) as title,
                event_types.name as type,
                date as start,
                DATE_ADD(date, INTERVAL 1 HOUR) as end
            ");

        $events = $maintenanceEvents
            ->unionAll($calibrationEvents)
            ->unionAll($eventLogs)
            ->get();

        return response()->json($events);
    }
}
