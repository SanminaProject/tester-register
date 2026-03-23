<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TesterCustomer extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'contact_person',
        'phone',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all testers for this customer
     */
    public function testers(): HasMany
    {
        return $this->hasMany(Tester::class, 'customer_id');
    }
}
