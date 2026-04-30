<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleStatus extends Model
{
    use HasFactory;

    protected $table = 'schedule_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}