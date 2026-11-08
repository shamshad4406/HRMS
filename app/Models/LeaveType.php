<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'days_per_year',
        'is_paid',
        'requires_approval',
        'requires_document',
        'is_carry_forward',
        'max_carry_forward_days',
        'is_encashable',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_document' => 'boolean',
        'is_carry_forward' => 'boolean',
        'is_encashable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
