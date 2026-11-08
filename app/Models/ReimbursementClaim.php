<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementClaim extends Model
{
    protected $fillable = [
        'personal_information_id',
        'payroll_id',
        'category_id',
        'claim_date',
        'amount',
        'description',
        'expense_date',
        'receipt_path',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'payment_date',
        'payment_method',
        'payment_reference',
        'remarks',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class, 'personal_information_id');
    }

    public function category()
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(ReimbursementItem::class, 'claim_id');
    }
}
