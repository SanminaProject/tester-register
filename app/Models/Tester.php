<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tester extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'model',
        'serial_number',
        'purchase_date',
        'status',
        'location',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

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
    }
}
