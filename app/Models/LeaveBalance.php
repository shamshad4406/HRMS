<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'personal_information_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
        'carried_forward_days',
    ];

    protected $casts = [
        'total_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
        'carried_forward_days' => 'decimal:2',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
