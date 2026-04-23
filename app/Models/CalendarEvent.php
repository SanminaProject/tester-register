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
            ->leftJoin('users', 'tester_maintenance_schedules.next_maintenance_by_user_id', '=', 'users.id')
            ->leftJoin('schedule_statuses', 'tester_maintenance_schedules.maintenance_status', '=', 'schedule_statuses.id')
            ->selectRaw("
                CONCAT('M-', LPAD(tester_maintenance_schedules.id, 4, '0')) as id,
                testers.id as tester_id,
                CONCAT(
                    'M-', LPAD(tester_maintenance_schedules.id, 4, '0')
                ) as title,
                'maintenance' as type,
                tester_maintenance_schedules.id as event_ref_id,
                CONCAT('M-', LPAD(tester_maintenance_schedules.id, 4, '0')) as event_code,
                COALESCE(testers.name, CONCAT('Tester #', testers.id)) as tester_name,
                'Maintenance' as maintenance_calibration,
                TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) as user_name,
                schedule_statuses.name as event_status,
                next_maintenance_due as start,
                DATE_ADD(next_maintenance_due, INTERVAL 1 HOUR) as end
            ");
    }

    protected static function calibrationEventsQuery()
    {
        return DB::table('tester_calibration_schedules')
            ->join('testers', 'tester_calibration_schedules.tester_id', '=', 'testers.id')
            ->leftJoin('users', 'tester_calibration_schedules.next_calibration_by_user_id', '=', 'users.id')
            ->leftJoin('schedule_statuses', 'tester_calibration_schedules.calibration_status', '=', 'schedule_statuses.id')
            ->selectRaw("
                CONCAT('C-', LPAD(tester_calibration_schedules.id, 4, '0')) as id,
                testers.id as tester_id,
                CONCAT(
                    'C-', LPAD(tester_calibration_schedules.id, 4, '0')
                ) as title,
                'calibration' as type,
                tester_calibration_schedules.id as event_ref_id,
                CONCAT('C-', LPAD(tester_calibration_schedules.id, 4, '0')) as event_code,
                COALESCE(testers.name, CONCAT('Tester #', testers.id)) as tester_name,
                'Calibration' as maintenance_calibration,
                TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) as user_name,
                schedule_statuses.name as event_status,
                next_calibration_due as start,
                DATE_ADD(next_calibration_due, INTERVAL 1 HOUR) as end
            ");
    }

    protected static function eventLogsQuery()
    {
        return DB::table('tester_event_logs')
            ->join('event_types', 'tester_event_logs.event_type', '=', 'event_types.id')
            ->join('testers', 'tester_event_logs.tester_id', '=', 'testers.id')
            ->leftJoin('users', 'tester_event_logs.created_by_user_id', '=', 'users.id')
            ->whereIn(DB::raw('LOWER(event_types.name)'), ['maintenance', 'calibration'])
            ->selectRaw("
                CONCAT('E-', LPAD(tester_event_logs.id, 4, '0')) as id,
                testers.id as tester_id,
                CONCAT(
                    'E-', LPAD(tester_event_logs.id, 4, '0')
                ) as title,
                event_types.name as type,
                tester_event_logs.id as event_ref_id,
                CONCAT('E-', LPAD(tester_event_logs.id, 4, '0')) as event_code,
                COALESCE(testers.name, CONCAT('Tester #', testers.id)) as tester_name,
                CASE
                    WHEN LOWER(event_types.name) = 'calibration' THEN 'Calibration'
                    ELSE 'Maintenance'
                END as maintenance_calibration,
                TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) as user_name,
                'Completed' as event_status,
                date as start,
                DATE_ADD(date, INTERVAL 1 HOUR) as end
            ");
    }
}
