<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementItem extends Model
{
    protected $fillable = [
        'claim_id',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'total_amount',
        'receipt_path',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function claim()
    {
        return $this->belongsTo(ReimbursementClaim::class, 'claim_id');
    }
}
