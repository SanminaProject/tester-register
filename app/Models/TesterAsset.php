<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesterAsset extends Model
{
    use HasFactory;

    protected $table = 'tester_assets';

    public $timestamps = false;

    protected $fillable = [
        'asset_no',
        'tester_id',
    ];
}
