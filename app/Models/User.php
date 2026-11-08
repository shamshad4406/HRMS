<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'personal_information_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCto(): bool
    {
        return $this->role === 'cto';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function approvedPayrolls()
    {
        return $this->hasMany(\App\Models\Payroll::class, 'approved_by');
    }

    public function processedPayments()
    {
        return $this->hasMany(\App\Models\PayrollPayment::class, 'processed_by');
    }

    public function personalInformation()
    {
        return $this->belongsTo(\App\Models\PersonalInformation::class, 'personal_information_id');
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(\App\Models\Notification::class)->where('is_read', false)->orderBy('created_at', 'desc');
    }

    public function subordinates()
    {
        if (!$this->personal_information_id) {
            return collect();
        }

        return \App\Models\EmploymentDetail::where('reporting_manager_id', $this->personal_information_id)
            ->with('personalInformation')
            ->get();
    }

    public function canApproveLeave(\App\Models\Leave $leave): bool
    {
        // Admin can approve all leaves
        if ($this->isAdmin()) {
            return true;
        }

        // Manager can only approve leaves of their subordinates
        if ($this->isManager() && $this->personal_information_id) {
            $subordinateIds = $this->subordinates()->pluck('personal_information_id')->toArray();
            return in_array($leave->personal_information_id, $subordinateIds);
        }

        // Employees cannot approve leaves
        return false;
    }
}
