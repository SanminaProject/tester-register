<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'serial_number',
        'tester_id',
        'purchase_date',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tester that owns this fixture
     */
    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class, 'tester_id');
    }
}
