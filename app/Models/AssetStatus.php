<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetStatus extends Model
{
    protected $table = 'asset_statuses';
    public $timestamps = false;
    protected $fillable = ['name'];
}
