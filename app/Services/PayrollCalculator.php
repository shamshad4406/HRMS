<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\PayrollSetting;
use App\Models\PersonalInformation;
use App\Models\SalaryDetail;
use Carbon\Carbon;

/**
 * Calculates the automatic parts of a payroll: allowances from the salary structure,
 * statutory deductions (PF / ESI / professional tax / TDS) and attendance based
 * deductions (unpaid leave, absent days, late marks).
 *
 * Deduction line types use the existing payroll_deduction_details enum:
 * 'pf', 'esi', 'tax' (professional tax and TDS) and 'leave' (unpaid leave, absent, late).
 */
class PayrollCalculator
{
    public function calculate(PersonalInformation $employee, SalaryDetail $salary, string $month, string $year): array
    {
        $settings = PayrollSetting::current();

        $basic = (float) $salary->basic_salary;
        $allowances = $salary->fixedAllowances();
        $gross = $basic + $allowances;

        $lines = [];
        $summary = ['per_day' => 0.0, 'unpaid_leave_days' => 0.0, 'absent_days' => 0.0, 'late_marks' => 0];

        // ---- Statutory deductions -------------------------------------------------
        if ($salary->pf_applicable) {
            $wage = $settings->pf_wage_ceiling > 0 ? min($basic, $settings->pf_wage_ceiling) : $basic;
            $this->addLine($lines, 'pf', $wage * $settings->pf_rate / 100, "PF ({$settings->pf_rate}% of ₹" . number_format($wage, 2) . ')');
        }

        if ($salary->esi_applicable && ($settings->esi_gross_limit <= 0 || $gross <= $settings->esi_gross_limit)) {
            $this->addLine($lines, 'esi', $gross * $settings->esi_rate / 100, "ESI ({$settings->esi_rate}% of gross)");
        }

        if ($salary->pt_applicable) {
            $this->addLine($lines, 'tax', $settings->professional_tax, 'Professional tax');
        }

        if ((float) $salary->tds_monthly > 0) {
            $this->addLine($lines, 'tax', (float) $salary->tds_monthly, 'TDS (monthly)');
        }

        // ---- Attendance / leave based deductions ------------------------------------
        try {
            $monthStart = Carbon::parse('1 ' . $month . ' ' . $year)->startOfMonth();
        } catch (\Throwable $e) {
            return $this->result($allowances, $lines, $summary); // unknown month text: skip attendance rules
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        $weeklyOff = $settings->weeklyOffNumbers();
        $holidays = $this->holidaySet($monthStart, $monthEnd);

        $isWorkingDay = function (Carbon $date) use ($weeklyOff, $holidays) {
            return !in_array($date->dayOfWeek, $weeklyOff, true)
                && !isset($holidays[$date->format('Y-m-d')]);
        };

        $workingDays = 0;
        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            if ($isWorkingDay($d)) {
                $workingDays++;
            }
        }

        $divisor = $settings->salary_divisor === 'working_days' ? max(1, $workingDays) : $monthStart->daysInMonth;
        $perDay = $gross / $divisor;
        $summary['per_day'] = round($perDay, 2);

        // Approved leaves overlapping the month
        $leaves = Leave::where('personal_information_id', $employee->id)
            ->where('status', 'approved')
            ->where('from_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('to_date', '>=', $monthStart->format('Y-m-d'))
            ->get();

        $onLeave = [];       // date => fraction of the day covered by approved leave
        $unpaidLeaveDays = 0.0;

        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->from_date)->max($monthStart);
            $to = Carbon::parse($leave->to_date)->min($monthEnd);
            $fraction = $leave->duration === 'half_day' ? 0.5 : 1.0;

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                if (!$isWorkingDay($d)) {
                    continue;
                }
                $key = $d->format('Y-m-d');
                $onLeave[$key] = min(1.0, ($onLeave[$key] ?? 0) + $fraction);
                if (!$leave->is_paid) {
                    $unpaidLeaveDays += $fraction;
                }
            }
        }

        if ($settings->unpaid_leave_deduction && $unpaidLeaveDays > 0) {
            $summary['unpaid_leave_days'] = $unpaidLeaveDays;
            $this->addLine($lines, 'leave', $unpaidLeaveDays * $perDay, "Unpaid leave: {$unpaidLeaveDays} day(s) x ₹" . number_format($perDay, 2));
        }

        // Attendance rows of the month
        $attendances = Attendance::where('personal_information_id', $employee->id)
            ->whereBetween('attendance_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->get()
            ->groupBy(fn ($a) => Carbon::parse($a->attendance_date)->format('Y-m-d'));

        if ($settings->absent_deduction) {
            $joining = optional($employee->employmentDetail)->joining_date;
            $from = $joining ? Carbon::parse($joining)->max($monthStart) : $monthStart->copy();
            $to = $monthEnd->copy()->min(Carbon::yesterday());
            $absentDays = 0.0;

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                if (!$isWorkingDay($d)) {
                    continue;
                }
                $key = $d->format('Y-m-d');
                $covered = $onLeave[$key] ?? 0.0; // approved leave (paid or unpaid) is handled above

                $rows = $attendances->get($key);
                if ($rows && $rows->contains(fn ($a) => in_array($a->status, ['present', 'late'], true))) {
                    continue; // worked that day
                }

                $missing = ($rows && $rows->contains('status', 'half-day')) ? 0.5 : 1.0;
                $absentDays += max(0.0, $missing - $covered);
            }

            if ($absentDays > 0) {
                $summary['absent_days'] = $absentDays;
                $this->addLine($lines, 'leave', $absentDays * $perDay, "Absent: {$absentDays} day(s) x ₹" . number_format($perDay, 2));
            }
        }

        if ($settings->late_marks_per_deduction > 0) {
            $lateMarks = $attendances->flatten(1)->where('status', 'late')->count();
            $summary['late_marks'] = $lateMarks;
            $blocks = intdiv($lateMarks, $settings->late_marks_per_deduction);

            if ($blocks > 0 && $settings->late_deduction_days > 0) {
                $days = $blocks * $settings->late_deduction_days;
                $this->addLine($lines, 'leave', $days * $perDay, "Late marks: {$lateMarks} ({$days} day(s) deducted)");
            }
        }

        return $this->result($allowances, $lines, $summary);
    }

    private function addLine(array &$lines, string $type, float $amount, string $description): void
    {
        $amount = round($amount, 2);

        if ($amount > 0) {
            $lines[] = ['type' => $type, 'amount' => $amount, 'description' => $description];
        }
    }

    private function result(float $allowances, array $lines, array $summary): array
    {
        return [
            'allowances' => round($allowances, 2),
            'lines' => $lines,
            'total_deductions' => round(array_sum(array_column($lines, 'amount')), 2),
            'summary' => $summary,
        ];
    }

    /** Active holidays of the month as a Y-m-d lookup (recurring holidays match by month/day). */
    private function holidaySet(Carbon $start, Carbon $end): array
    {
        $set = [];

        foreach (Holiday::where('is_active', true)->get() as $holiday) {
            $date = Carbon::parse($holiday->date);

            if ($holiday->is_recurring) {
                $date = Carbon::create($start->year, $date->month, $date->day);
            }

            if ($date->between($start, $end)) {
                $set[$date->format('Y-m-d')] = true;
            }
        }

        return $set;
    }
}
