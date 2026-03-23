<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    protected $fillable = [
        'tester_id',
        'type',
        'description',
        'event_date',
        'recorded_by',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tester that owns this event log
     */
    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class, 'tester_id');
    }
}
