<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesterCalibrationProcedure extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'interval_value',
        'description',
        'interval_unit',
    ];
}