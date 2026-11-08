<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PersonalInformation;
use App\Models\EmploymentDetail;
use App\Models\SalaryDetail;
use App\Models\BankDetail;
use App\Models\EmployeeDocument;
use App\Models\EmergencyContact;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the current max ID to start from
        $maxId = DB::table('personal_information')->max('id') ?? 0;

        // Generate unique employee codes
        for ($i = 1; $i <= 10; $i++) {
            $nextId = $maxId + $i;
            $employeeCode = 'INN' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            PersonalInformation::create([
                'employee_code' => $employeeCode,
                'first_name' => 'Test',
                'last_name' => 'Employee ' . $i,
                'gender' => 'male',
                'date_of_birth' => '1990-01-01',
                'email' => 'test' . $i . '@example.com',
                'aadhaar_number' => str_pad(rand(100000000000, 999999999999), 12, '0'),
                'pan_number' => strtoupper(substr(md5($i), 0, 10)),
                'mobile_number' => '9876543210',
            ]);
        }
    }
}
