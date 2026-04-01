<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'part_number',
        'quantity_in_stock',
        'unit_cost',
        'supplier',
        'stock_status',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_in_stock <= 5) {
            return 'low';
        } elseif ($this->quantity_in_stock <= 20) {
            return 'normal';
        } else {
            return 'full';
        }
    }
}
