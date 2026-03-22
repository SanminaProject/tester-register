DELIMITER $$

CREATE TRIGGER trigger_after_insert_maintenance_or_calibration_event
AFTER INSERT ON tester_event_logs
FOR EACH ROW
BEGIN
    IF NEW.event_type = 'maintenance' AND NEW.maintenance_schedule_id IS NOT NULL THEN

        UPDATE tester_maintenance_schedules
        SET
            last_maintenance_date = NEW.event_date,
            last_maintenance_by_user_id = NEW.created_by_user_id,
            maintenance_status = 'Scheduled'
        WHERE maintenance_schedule_id = NEW.maintenance_schedule_id;

    END IF;

    IF NEW.event_type = 'calibration' AND NEW.calibration_schedule_id IS NOT NULL THEN

        UPDATE tester_calibration_schedules
        SET
            last_calibration_date = NEW.event_date,
            last_calibration_by_user_id = NEW.created_by_user_id,
            calibration_status = 'Scheduled'
        WHERE calibration_schedule_id = NEW.calibration_schedule_id;

    END IF;
END$$

DELIMITER ;