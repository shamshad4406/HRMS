<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'personal_information_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'duration',
        'total_days',
        'reason',
        'attachment',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'remarks',
        'is_paid',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_days' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
