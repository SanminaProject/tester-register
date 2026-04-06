<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TesterEventLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $timestamps = false;

    protected $fillable = [
        'date',
        'description',
        'tester_id',
        'event_type',
        'created_by_user_id',
        'maintenance_schedule_id',
        'calibration_schedule_id',
        'resolved_date',
        'resolution_description',
        'resolved_by_user_id',
        'issue_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'resolved_date' => 'datetime',
        ];
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class);
    }
}
