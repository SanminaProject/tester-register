<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetStatus extends Model
{
    use HasFactory;

    protected $table = 'asset_statuses';
    public $timestamps = false;
    protected $fillable = ['name'];
}
