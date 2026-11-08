<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'personal_information_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'captured_face',
        'face_match_score',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }
}
