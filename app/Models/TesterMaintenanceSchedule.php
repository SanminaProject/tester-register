<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TesterMaintenanceSchedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'schedule_created_date',
        'last_maintenance_date',
        'next_maintenance_due',
        'tester_id',
        'maintenance_id',
        'maintenance_status',
        'last_maintenance_by_user_id',
        'next_maintenance_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'schedule_created_date' => 'date',
            'last_maintenance_date' => 'date',
            'next_maintenance_due' => 'date',
        ];
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class);
    }
}
