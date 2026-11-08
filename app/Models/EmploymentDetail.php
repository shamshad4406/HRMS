<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class EmploymentDetail extends Model
{
    protected $fillable = [
        'personal_information_id',
        'department',
        'designation',
        'employment_type',
        'joining_date',
        'reporting_manager_id',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($employmentDetail) {
            // When employment detail is created, sync user's is_active status
            $user = User::where('personal_information_id', $employmentDetail->personal_information_id)->first();
            if ($user) {
                $user->update([
                    'is_active' => $employmentDetail->status === 'active'
                ]);
            }
        });

        static::updated(function ($employmentDetail) {
            // If status changed, update user's is_active status
            if ($employmentDetail->isDirty('status')) {
                $user = User::where('personal_information_id', $employmentDetail->personal_information_id)->first();
                if ($user) {
                    $user->update([
                        'is_active' => $employmentDetail->status === 'active'
                    ]);
                }
            }
        });
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(PersonalInformation::class, 'reporting_manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(EmploymentDetail::class, 'reporting_manager_id', 'personal_information_id');
    }
}
