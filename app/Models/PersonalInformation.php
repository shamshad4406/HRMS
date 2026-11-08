<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'aadhaar_number',
        'pan_number',
        'mobile_number',
        'photo',
        'registration_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_status' => 'string',
    ];

    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    public function salaryDetail()
    {
        return $this->hasOne(SalaryDetail::class);
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class);
    }

    public function employeeDocument()
    {
        return $this->hasOne(EmployeeDocument::class);
    }

    public function emergencyContact()
    {
        return $this->hasOne(EmergencyContact::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'personal_information_id');
    }

    public function reimbursementClaims()
    {
        return $this->hasMany(ReimbursementClaim::class, 'personal_information_id');
    }
}
