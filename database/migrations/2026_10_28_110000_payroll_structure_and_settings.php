<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salary structure per employee (all optional, default 0 / off => old behaviour)
        Schema::table('salary_details', function (Blueprint $table) {
            $table->decimal('hra', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('special_allowance', 12, 2)->default(0)->after('hra');
            $table->decimal('other_allowance', 12, 2)->default(0)->after('special_allowance');
            $table->boolean('pf_applicable')->default(false)->after('currency');
            $table->boolean('esi_applicable')->default(false)->after('pf_applicable');
            $table->boolean('pt_applicable')->default(false)->after('esi_applicable');
            $table->decimal('tds_monthly', 12, 2)->default(0)->after('pt_applicable');
        });

        // Company wide payroll rules (single row)
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('salary_divisor', ['calendar_days', 'working_days'])->default('calendar_days');
            $table->string('weekly_off_days')->default('sat,sun');
            $table->boolean('unpaid_leave_deduction')->default(true);
            $table->boolean('absent_deduction')->default(false);      // off by default: needs reliable attendance data
            $table->unsignedTinyInteger('late_marks_per_deduction')->default(0); // 0 = disabled
            $table->decimal('late_deduction_days', 4, 2)->default(0.5);
            $table->decimal('pf_rate', 5, 2)->default(12);
            $table->decimal('pf_wage_ceiling', 10, 2)->default(15000); // 0 = no ceiling
            $table->decimal('esi_rate', 5, 2)->default(0.75);          // employee share
            $table->decimal('esi_gross_limit', 10, 2)->default(21000); // 0 = no limit
            $table->decimal('professional_tax', 8, 2)->default(200);
            $table->timestamps();
        });

        DB::table('payroll_settings')->insert(['created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');

        Schema::table('salary_details', function (Blueprint $table) {
            $table->dropColumn(['hra', 'special_allowance', 'other_allowance', 'pf_applicable', 'esi_applicable', 'pt_applicable', 'tds_monthly']);
        });
    }
};
