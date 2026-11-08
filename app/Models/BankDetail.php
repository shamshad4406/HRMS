<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'personal_information_id',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch_name',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class);
    }
}
