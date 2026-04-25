<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'additional_info',
        'last_inventoried_date',
        'location_id',
        'owner_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'implementation_date' => 'date',
            'last_inventoried_date' => 'datetime',
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
        return $this->hasMany(TesterMaintenanceSchedule::class);
    }

    public function calibrationSchedules(): HasMany
    {
        return $this->hasMany(TesterCalibrationSchedule::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(TesterAsset::class, 'tester_id');
    }

    public function responsibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_tester_assignments',
            'tester_id',
            'user_id'
        );
    }
}
