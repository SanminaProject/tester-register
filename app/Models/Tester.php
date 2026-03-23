<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tester extends Model
{
    protected $fillable = [
        'model',
        'serial_number',
        'customer_id',
        'purchase_date',
        'status',
        'location',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns this tester
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(TesterCustomer::class, 'customer_id');
    }

    /**
     * Get all fixtures for this tester
     */
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'tester_id');
    }

    /**
     * Get all maintenance schedules for this tester
     */
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'tester_id');
    }

    /**
     * Get all calibration schedules for this tester
     */
    public function calibrationSchedules(): HasMany
    {
        return $this->hasMany(CalibrationSchedule::class, 'tester_id');
    }

    /**
     * Get all event logs for this tester
     */
    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class, 'tester_id');
    }
}
