<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TesterSparePartSupplier extends Model
{
    protected $table = 'tester_spare_part_suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the spare parts supplied by this supplier.
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(TesterSparePart::class, 'supplier_id');
    }
}

