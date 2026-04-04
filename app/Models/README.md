# Models Folder Structure
The `app/Models` folder contains models that map to the database tables and capture business relationships and logic.

```
app/Models/
├── CalibrationSchedule.php
├── Event.php
├── EventLog.php
├── Fixture.php
├── MaintenanceSchedule.php
├── SparePart.php
├── Tester.php
├── TesterCustomer.php
└── User.php
```

## Model summaries

### `User.php`
- Core Laravel auth user model (`Authenticatable`).
- Uses `HasApiTokens`, `HasFactory`, `Notifiable`, `HasRoles` (Spatie permissions).
- Attributes: `name`, `email`, `password`, `email_verified_at`, etc.

### `TesterCustomer.php`
- Represents the customer owning one or more testers.
- Fillable: `company_name`, `address`, `contact_person`, `phone`, `email`.
- Relation: `testers()` hasMany `Tester`.

### `Tester.php`
- Represents a tester instrument.
- Fillable: `customer_id`, `model`, `serial_number`, `purchase_date`, `status`, `location`, `notes`.
- Relations:
  - `customer()` belongsTo `TesterCustomer`
  - `fixtures()` hasMany `Fixture`
  - `maintenanceSchedules()` hasMany `MaintenanceSchedule`
  - `calibrationSchedules()` hasMany `CalibrationSchedule`
  - `eventLogs()` hasMany `EventLog`

### `Fixture.php`
- Hardware fixture linked to a tester.
- Fillable: `tester_id`, `name`, `serial_number`, `purchase_date`, `status`, `location`, `notes`.
- Casts: `purchase_date` as `date`.
- Relation: `tester()` belongsTo `Tester`.

### `MaintenanceSchedule.php`
- Maintenance tasks scheduled for a tester.
- Fillable: `tester_id`, `scheduled_date`, `status`, `procedure`, `completed_date`, `performed_by`, `notes`.
- Casts: `scheduled_date`, `completed_date` as `date`.
- Relation: `tester()` belongsTo `Tester`.

### `CalibrationSchedule.php`
- Calibration tasks scheduled for a tester.
- Same structure as maintenance schedules.
- Relation: `tester()` belongsTo `Tester`.

### `EventLog.php`
- Domain-specific event log record for a tester.
- Fillable: `tester_id`, `type`, `event_date`, `description`, `performed_by`, `metadata`.
- Casts: `event_date` as `datetime`, `metadata` as `array`.
- Relation: `tester()` belongsTo `Tester`.

### `SparePart.php`
- Inventory of spare parts.
- Fillable: `name`, `part_number`, `quantity_in_stock`, `unit_cost`, `supplier`, `notes`.
- Casts: `quantity_in_stock` as `integer`, `unit_cost` as `decimal:2`.
- Appends computed `stock_status` attribute:
  - `<= 5` => `low`
  - `<= 20` => `normal`
  - otherwise => `full`

