<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataChangeLog extends Model
{
    use HasFactory;

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
        if ($this->spare_part_id) return $this->spare_part_id;
        if ($this->spare_part_supplier_id) return $this->spare_part_supplier_id;

        if (preg_match('/Deleted (spare part|supplier) \\[ID: (\\d+)\\]/i', $this->explanation, $matches)) {
            return $matches[2];
        }

        return null;
    }

    public function getEntityNameAttribute()
    {
        if ($this->spare_part?->name) {
            return $this->spare_part->name;
        }

        if ($this->spare_part_supplier?->supplier_name) {
            return $this->spare_part_supplier->supplier_name;
        }

        if (preg_match('/Deleted supplier \[ID: \d+\] - Name: (.+)/i', $this->explanation, $matches)) {
            return $matches[1];
        }

        if (preg_match('/Deleted spare part \[ID: \d+\] - Name: (.+)/i', $this->explanation, $matches)) {
            return $matches[1];
        }

        return '—';
    }
}
