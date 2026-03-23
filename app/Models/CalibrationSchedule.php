<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalibrationSchedule extends Model
{
    protected $fillable = [
        'tester_id',
        'scheduled_date',
        'status',
        'procedure',
        'notes',
        'completed_date',
        'performed_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tester that owns this schedule
     */
    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class, 'tester_id');
    }
}
