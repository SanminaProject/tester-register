-- event used to calculate and update the next maintenance and calibration dates for testers

DELIMITER $$

CREATE EVENT update_next_service_dates
ON SCHEDULE EVERY 1 DAY -- runs automatically every day
DO
BEGIN

    -- update the next_maintenance_due based on last performed maintenance date or schedule creation date on every row
    UPDATE tester_maintenance_schedules tms
    JOIN tester_maintenance_procedures tmp
      ON tms.maintenance_id = tmp.maintenance_id
    SET
        tms.next_maintenance_due = CASE
            WHEN tmp.maintenance_interval_unit = 'Days' THEN DATE_ADD(IFNULL(tms.last_maintenance_date, tms.schedule_created_date), INTERVAL tmp.maintenance_interval_value DAY)
            WHEN tmp.maintenance_interval_unit = 'Weeks' THEN DATE_ADD(IFNULL(tms.last_maintenance_date, tms.schedule_created_date), INTERVAL tmp.maintenance_interval_value WEEK)
            WHEN tmp.maintenance_interval_unit = 'Months' THEN DATE_ADD(IFNULL(tms.last_maintenance_date, tms.schedule_created_date), INTERVAL tmp.maintenance_interval_value MONTH)
            WHEN tmp.maintenance_interval_unit = 'Years' THEN DATE_ADD(IFNULL(tms.last_maintenance_date, tms.schedule_created_date), INTERVAL tmp.maintenance_interval_value YEAR)
        END;

    -- update maintenance_status to 'Overdue' if next_maintenance_due has passed
    UPDATE tester_maintenance_schedules
    SET maintenance_status = 'Overdue'
    WHERE next_maintenance_due < NOW()
      AND maintenance_status = 'Scheduled';


    -- update the next next_calibration_due based on last performed calibration or schedule creation date on every row
    UPDATE tester_calibration_schedules tcs
    JOIN tester_calibration_procedures tcp
      ON tcs.calibration_id = tcp.calibration_id
    SET
        tcs.next_calibration_due = CASE
            WHEN tcp.calibration_interval_unit = 'Days' THEN DATE_ADD(IFNULL(tcs.last_calibration_date, tcs.schedule_created_date), INTERVAL tcp.calibration_interval_value DAY)
            WHEN tcp.calibration_interval_unit = 'Weeks' THEN DATE_ADD(IFNULL(tcs.last_calibration_date, tcs.schedule_created_date), INTERVAL tcp.calibration_interval_value WEEK)
            WHEN tcp.calibration_interval_unit = 'Months' THEN DATE_ADD(IFNULL(tcs.last_calibration_date, tcs.schedule_created_date), INTERVAL tcp.calibration_interval_value MONTH)
            WHEN tcp.calibration_interval_unit = 'Years' THEN DATE_ADD(IFNULL(tcs.last_calibration_date, tcs.schedule_created_date), INTERVAL tcp.calibration_interval_value YEAR)
        END;

    -- update calibration_status to 'Overdue' if next_calibration_due has passed
    UPDATE tester_calibration_schedules
    SET calibration_status = 'Overdue'
    WHERE next_calibration_due < NOW()
      AND calibration_status = 'Scheduled';

END$$

DELIMITER ;