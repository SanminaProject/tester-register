<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TesterSparePart extends Model
{
    protected $table = 'tester_spare_parts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'manufacturer_part_number',
        'quantity_in_stock',
        'reorder_level',
        'last_order_date',
        'unit_price',
        'description',
        'tester_id',
        'supplier_id',
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
            'reorder_level' => 'integer',
            'last_order_date' => 'date',
            'unit_price' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(Tester::class, 'tester_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(TesterSparePartSupplier::class, 'supplier_id');
    }
}
