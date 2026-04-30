<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcedureIntervalUnit extends Model
{
    use HasFactory;

    protected $table = 'procedure_interval_units';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}