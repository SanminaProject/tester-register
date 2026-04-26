<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataChangeLog extends Model
{
    public $timestamps = false;

    protected $table = 'data_change_logs';

    protected $fillable = [
        'changed_at',
        'explanation',
        'tester_id',
        'fixture_id',
        'spare_part_id',
        'spare_part_supplier_id',
        'user_id',
    ];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function tester()
    {
        return $this->belongsTo(Tester::class);
    }

    public function spare_part()
    {
        return $this->belongsTo(TesterSparePart::class);
    }

    public function spare_part_supplier(): BelongsTo
    {
        return $this->belongsTo(TesterSparePartSupplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getTypeAttribute(): string
    {
        if ($this->spare_part_id) return 'Spare Part';
        if ($this->spare_part_supplier_id) return 'Supplier';

        // Check explanation for deleted entities (foreign keys become null after deletion)
        if (str_contains(strtolower($this->explanation), 'spare part')) {
            return 'Spare Part';
        }
        if (str_contains(strtolower($this->explanation), 'supplier')) {
            return 'Supplier';
        }

        return 'Unknown';
    }

    public function getEntityIdAttribute()
    {
        return $this->spare_part_id 
            ?? $this->spare_part_supplier_id;
    }

    public function getEntityNameAttribute()
    {
        return $this->spare_part?->name
            ?? $this->spare_part_supplier?->supplier_name
            ?? '—';
    }
}
