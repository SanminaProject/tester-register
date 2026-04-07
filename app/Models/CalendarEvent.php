<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'tester_id',
        'title',
        'type',
        'start',
        'end',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public static function getCalendarEvents()
    {
        $maintenanceEvents = self::maintenanceEventsQuery();
        $calibrationEvents = self::calibrationEventsQuery();
        $eventLogs = self::eventLogsQuery();

        return $maintenanceEvents
            ->unionAll($calibrationEvents)
            ->unionAll($eventLogs)
            ->get();
    }

    protected static function maintenanceEventsQuery()
    {
        return DB::table('tester_maintenance_schedules')
            ->join('testers', 'tester_maintenance_schedules.tester_id', '=', 'testers.id')
            ->selectRaw("
                CONCAT('maintenance-', tester_maintenance_schedules.id) as id,
                testers.id as tester_id,
                CONCAT('Maintenance - ', testers.name) as title,
                'maintenance' as type,
                next_maintenance_due as start,
                DATE_ADD(next_maintenance_due, INTERVAL 1 HOUR) as end
            ");
    }

    protected static function calibrationEventsQuery()
    {
        return DB::table('tester_calibration_schedules')
            ->join('testers', 'tester_calibration_schedules.tester_id', '=', 'testers.id')
            ->selectRaw("
                CONCAT('calibration-', tester_calibration_schedules.id) as id,
                testers.id as tester_id,
                CONCAT('Calibration - ', testers.name) as title,
                'calibration' as type,
                next_calibration_due as start,
                DATE_ADD(next_calibration_due, INTERVAL 1 HOUR) as end
            ");
    }

    protected static function eventLogsQuery()
    {
        return DB::table('tester_event_logs')
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
    }
}
