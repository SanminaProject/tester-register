<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tester extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'id_number_by_customer',
        'operating_system',
        'type',
        'product_family',
        'manufacturer',
        'implementation_date',
        'location_id',
        'owner_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'implementation_date' => 'date',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(TesterAndFixtureLocation::class, 'location_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(TesterCustomer::class, 'owner_id');
    }


    public function statusRelation(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'status');
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function calibrationSchedules(): HasMany
    {
        return $this->hasMany(CalibrationSchedule::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }




    // -- Below values not needed for now, but should be taken into use if needed in the future --

    /* 
    public function customer(): BelongsTo
    {
        return $this->belongsTo(TesterCustomer::class, 'customer_id');
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function calibrationSchedules(): HasMany
    {
        return $this->hasMany(CalibrationSchedule::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    } */
}
