<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TesterSparePart extends Model
{
    protected $table = 'tester_spare_parts';
    public $timestamps = false;

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

    public function getNeedsReorderAttribute()
    {
        return $this->quantity_in_stock !== null
            && $this->reorder_level !== null
            && $this->quantity_in_stock <= $this->reorder_level;
    }

    public function responsibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_spare_part_assignments',
            'spare_part_id',
            'user_id'
        );
    }

    public function getResponsibleUserNamesAttribute(): ?string
    {
        $names = $this->responsibleUsers
            ->map(fn ($user) => $user->first_name . ' ' . $user->last_name)
            ->join(', ');
        
        return $names !== '' ? $names : null;
    }

    public function getTesterResponsibleUserNamesAttribute(): ?string
    {
        $names = $this->tester?->responsibleUsers
            ->map(fn ($user) => $user->first_name . ' ' . $user->last_name)
            ->join(', ');
        
        return $names !== '' ? $names : null;
    }
}
