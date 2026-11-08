<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDetail extends Model
{
    protected $fillable = [
        'personal_information_id',
        'basic_salary',
        'hra',
        'special_allowance',
        'other_allowance',
        'salary_type',
        'currency',
        'pf_applicable',
        'esi_applicable',
        'pt_applicable',
        'tds_monthly',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'tds_monthly' => 'decimal:2',
        'pf_applicable' => 'boolean',
        'esi_applicable' => 'boolean',
        'pt_applicable' => 'boolean',
    ];

    /** Fixed monthly allowances from the salary structure. */
    public function fixedAllowances(): float
    {
        return (float) $this->hra + (float) $this->special_allowance + (float) $this->other_allowance;
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }
}
