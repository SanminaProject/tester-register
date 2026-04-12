<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'manufacturer',
        'tester_id',
        'location_id',
        'fixture_status',
    ];

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class);
    }
}
