<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TesterMaintenanceProcedure extends Model
{
    use HasFactory;

    protected $table = 'tester_maintenance_procedures';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'interval_value',
        'description',
        'interval_unit',
    ];

    public function intervalUnit(): BelongsTo
    {
        return $this->belongsTo(ProcedureIntervalUnit::class, 'interval_unit');
    }
}