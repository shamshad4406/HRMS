<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = [
        'salary_divisor',
        'weekly_off_days',
        'unpaid_leave_deduction',
        'absent_deduction',
        'late_marks_per_deduction',
        'late_deduction_days',
        'pf_rate',
        'pf_wage_ceiling',
        'esi_rate',
        'esi_gross_limit',
        'professional_tax',
    ];

    protected $casts = [
        'unpaid_leave_deduction' => 'boolean',
        'absent_deduction' => 'boolean',
        'late_marks_per_deduction' => 'integer',
        'late_deduction_days' => 'float',
        'pf_rate' => 'float',
        'pf_wage_ceiling' => 'float',
        'esi_rate' => 'float',
        'esi_gross_limit' => 'float',
        'professional_tax' => 'float',
    ];

    /** The single settings row (created with defaults if missing). */
    public static function current(): self
    {
        $setting = static::query()->first();

        if (!$setting) {
            static::query()->create([]);
            $setting = static::query()->first();
        }

        return $setting;
    }

    /** Weekly off days as Carbon day numbers (0 = Sunday ... 6 = Saturday). */
    public function weeklyOffNumbers(): array
    {
        $map = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $numbers = [];

        foreach (explode(',', strtolower((string) $this->weekly_off_days)) as $day) {
            $day = trim($day);
            if (isset($map[$day])) {
                $numbers[] = $map[$day];
            }
        }

        return $numbers;
    }
}
