<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    protected $fillable = [
        'payroll_id',
        'payment_method',
        'payment_reference',
        'payment_date',
        'amount',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'processed_by');
    }
}
