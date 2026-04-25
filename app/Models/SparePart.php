<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// TODO: delete this file? Unused?

class SparePart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'part_number',
        'quantity_in_stock',
        'unit_cost',
        'supplier',
        'notes',
    ];

    /**
     * The accessors to append to model arrays.
     *
     * @var list<string>
     */
    protected $appends = [
        'stock_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_in_stock' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_in_stock <= 5) {
            return 'low';
        }

        if ($this->quantity_in_stock <= 20) {
            return 'normal';
        }

        return 'full';
    }
}
