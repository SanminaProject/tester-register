<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TesterCalibrationSchedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'schedule_created_date',
        'last_calibration_date',
        'next_calibration_due',
        'tester_id',
        'calibration_id',
        'calibration_status',
        'last_calibration_by_user_id',
        'next_calibration_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'schedule_created_date' => 'date',
            'last_calibration_date' => 'date',
            'next_calibration_due' => 'date',
        ];
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class);
    }
}
