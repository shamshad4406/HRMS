<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'max_amount_per_claim',
        'max_amount_per_month',
        'requires_receipt',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'max_amount_per_claim' => 'decimal:2',
        'max_amount_per_month' => 'decimal:2',
        'requires_receipt' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function claims()
    {
        return $this->hasMany(ReimbursementClaim::class, 'category_id');
    }
}
