<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get admin's personal_information_id
        $adminPersonalInfoId = DB::table('users')->where('role', 'admin')->value('personal_information_id');

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear payroll payments
        DB::table('payroll_payments')->truncate();

        // Clear payroll deduction details
        DB::table('payroll_deduction_details')->truncate();

        // Clear payrolls
        DB::table('payrolls')->truncate();

        // Clear reimbursement items
        DB::table('reimbursement_items')->truncate();

        // Clear reimbursement claims
        DB::table('reimbursement_claims')->truncate();

        // Clear leave balances
        DB::table('leave_balances')->truncate();

        // Clear leaves
        DB::table('leaves')->truncate();

        // Clear attendance
        DB::table('attendances')->truncate();

        // Clear employee documents
        DB::table('employee_documents')->truncate();

        // Clear emergency contacts
        DB::table('emergency_contacts')->truncate();

        // Clear bank details (except admin's if exists)
        if ($adminPersonalInfoId) {
            DB::table('bank_details')->where('personal_information_id', '!=', $adminPersonalInfoId)->delete();
        } else {
            DB::table('bank_details')->truncate();
        }

        // Clear salary details (except admin's if exists)
        if ($adminPersonalInfoId) {
            DB::table('salary_details')->where('personal_information_id', '!=', $adminPersonalInfoId)->delete();
        } else {
            DB::table('salary_details')->truncate();
        }

        // Clear employment details (except admin's if exists)
        if ($adminPersonalInfoId) {
            DB::table('employment_details')->where('personal_information_id', '!=', $adminPersonalInfoId)->delete();
        } else {
            DB::table('employment_details')->truncate();
        }

        // Clear personal information (except admin's)
        if ($adminPersonalInfoId) {
            DB::table('personal_information')->where('id', '!=', $adminPersonalInfoId)->delete();
        } else {
            DB::table('personal_information')->truncate();
        }

        // Clear users (except admin)
        DB::table('users')->where('role', '!=', 'admin')->delete();

        // Clear notifications
        DB::table('notifications')->truncate();

        // Clear reimbursement categories
        DB::table('reimbursement_categories')->truncate();

        // Clear leave types
        DB::table('leave_types')->truncate();

        // Clear holidays
        DB::table('holidays')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as it deletes data
        // In production, you would restore from a backup
    }
};
