<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Payroll extends Model
{
    protected $fillable = [
        'personal_information_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'payment_date',
        'status',
        'approved_by',
        'approved_at',
        'approval_status',
        'generated_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }

    public function reimbursementClaims()
    {
        return $this->hasMany(ReimbursementClaim::class, 'payroll_id');
    }

    public function deductionDetails()
    {
        return $this->hasMany(PayrollDeductionDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(PayrollPayment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
