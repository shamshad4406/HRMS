<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDeductionDetail extends Model
{
    protected $fillable = [
        'payroll_id',
        'deduction_type',
        'amount',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
