<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\PersonalInformation;

class LeaveDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert Leave Types for 2026
        $leaveTypes = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'description' => 'Leave for personal reasons and short-term needs',
                'days_per_year' => 10,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => false,
                'is_carry_forward' => true,
                'max_carry_forward_days' => 3,
                'is_encashable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Leave for illness or medical reasons',
                'days_per_year' => 12,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => true,
                'is_carry_forward' => true,
                'max_carry_forward_days' => 5,
                'is_encashable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Earned Leave',
                'code' => 'EL',
                'description' => 'Accumulated leave based on service period',
                'days_per_year' => 15,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => false,
                'is_carry_forward' => true,
                'max_carry_forward_days' => 10,
                'is_encashable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Leave for female employees during pregnancy and childbirth',
                'days_per_year' => 90,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => true,
                'is_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'is_encashable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Leave for male employees during childbirth of their spouse',
                'days_per_year' => 15,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => true,
                'is_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'is_encashable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Compensatory Off',
                'code' => 'CO',
                'description' => 'Leave earned for working on holidays or weekends',
                'days_per_year' => 5,
                'is_paid' => true,
                'requires_approval' => true,
                'requires_document' => false,
                'is_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'is_encashable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Loss of Pay',
                'code' => 'LOP',
                'description' => 'Unpaid leave when other leaves are exhausted',
                'days_per_year' => 0,
                'is_paid' => false,
                'requires_approval' => true,
                'requires_document' => false,
                'is_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'is_encashable' => false,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('Leave types inserted successfully!');

        // Insert Holidays for 2026
        $holidays2026 = [
            [
                'name' => 'New Year\'s Day',
                'date' => '2026-01-01',
                'type' => 'public_holiday',
                'description' => 'New Year celebration',
                'is_recurring' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Republic Day',
                'date' => '2026-01-26',
                'type' => 'public_holiday',
                'description' => 'India Republic Day',
                'is_recurring' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maha Shivaratri',
                'date' => '2026-02-16',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Holi',
                'date' => '2026-03-04',
                'type' => 'public_holiday',
                'description' => 'Festival of colors',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Good Friday',
                'date' => '2026-04-03',
                'type' => 'public_holiday',
                'description' => 'Christian festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ram Navami',
                'date' => '2026-04-06',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Mahavir Jayanti',
                'date' => '2026-04-10',
                'type' => 'public_holiday',
                'description' => 'Jain festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Easter Sunday',
                'date' => '2026-04-05',
                'type' => 'public_holiday',
                'description' => 'Christian festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'May Day',
                'date' => '2026-05-01',
                'type' => 'public_holiday',
                'description' => 'Labour Day',
                'is_recurring' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Buddha Purnima',
                'date' => '2026-05-11',
                'type' => 'public_holiday',
                'description' => 'Buddhist festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Independence Day',
                'date' => '2026-08-15',
                'type' => 'public_holiday',
                'description' => 'India Independence Day',
                'is_recurring' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Janmashtami',
                'date' => '2026-08-17',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ganesh Chaturthi',
                'date' => '2026-09-09',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Onam',
                'date' => '2026-09-08',
                'type' => 'public_holiday',
                'description' => 'Kerala harvest festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Dussehra',
                'date' => '2026-10-03',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Diwali',
                'date' => '2026-10-20',
                'type' => 'public_holiday',
                'description' => 'Festival of lights',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Govardhan Puja',
                'date' => '2026-10-21',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Bhai Dooj',
                'date' => '2026-10-22',
                'type' => 'public_holiday',
                'description' => 'Hindu festival',
                'is_recurring' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Christmas',
                'date' => '2026-12-25',
                'type' => 'public_holiday',
                'description' => 'Christmas celebration',
                'is_recurring' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Company Annual Day',
                'date' => '2026-06-15',
                'type' => 'company_holiday',
                'description' => 'Company annual celebration',
                'is_recurring' => true,
                'is_active' => true,
            ],
        ];

        foreach ($holidays2026 as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }

        $this->command->info('2026 Holidays inserted successfully!');

        // Initialize Leave Balances for 2026 for all employees
        $year = 2026;
        $employees = PersonalInformation::all();
        $activeLeaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($employees as $employee) {
            foreach ($activeLeaveTypes as $leaveType) {
                $existingBalance = LeaveBalance::where('personal_information_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->first();

                if (!$existingBalance) {
                    LeaveBalance::create([
                        'personal_information_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                        'total_days' => $leaveType->days_per_year,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->days_per_year,
                        'carried_forward_days' => 0,
                    ]);
                }
            }
        }

        $this->command->info('2026 Leave balances initialized for all employees!');
        $this->command->info('2026 Leave data seeding completed successfully!');
    }
}
