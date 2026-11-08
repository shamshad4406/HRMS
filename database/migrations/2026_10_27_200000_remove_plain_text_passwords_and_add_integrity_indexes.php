<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Remove the plain-text password column (passwords must only ever be stored hashed).
     * 2. Add indexes the reports/lists need, and a unique key so a payroll cannot be
     *    generated twice for the same employee/month/year.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'plain_text_password')) {
            // Wipe the values first so they are gone even if a DB backup/replica keeps the column for a while
            DB::table('users')->update(['plain_text_password' => null]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('plain_text_password');
            });
        }

        // Unique payroll per employee/month/year (skipped with a warning if duplicates already exist)
        if (!Schema::hasIndex('payrolls', 'payrolls_employee_month_year_unique')) {
            $duplicates = DB::table('payrolls')
                ->select('personal_information_id', 'month', 'year')
                ->groupBy('personal_information_id', 'month', 'year')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($duplicates === 0) {
                Schema::table('payrolls', function (Blueprint $table) {
                    $table->unique(['personal_information_id', 'month', 'year'], 'payrolls_employee_month_year_unique');
                });
            } else {
                echo "  WARNING: duplicate payrolls found - unique index not added. Clean them up and re-run.\n";
            }
        }

        $this->addIndex('attendances', ['personal_information_id', 'attendance_date'], 'attendances_employee_date_index');
        $this->addIndex('attendances', ['attendance_date'], 'attendances_date_index');
        $this->addIndex('leaves', ['personal_information_id', 'status'], 'leaves_employee_status_index');
        $this->addIndex('leaves', ['from_date', 'to_date'], 'leaves_date_range_index');
        $this->addIndex('payrolls', ['approval_status', 'status'], 'payrolls_approval_status_index');
        $this->addIndex('reimbursement_claims', ['status'], 'reimbursement_claims_status_index');
        $this->addIndex('reimbursement_claims', ['personal_information_id', 'category_id', 'expense_date'], 'reimbursement_claims_limit_check_index');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'plain_text_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('plain_text_password')->nullable()->after('password');
            });
        }

        $this->dropIndex('payrolls', 'payrolls_employee_month_year_unique', true);
        $this->dropIndex('attendances', 'attendances_employee_date_index');
        $this->dropIndex('attendances', 'attendances_date_index');
        $this->dropIndex('leaves', 'leaves_employee_status_index');
        $this->dropIndex('leaves', 'leaves_date_range_index');
        $this->dropIndex('payrolls', 'payrolls_approval_status_index');
        $this->dropIndex('reimbursement_claims', 'reimbursement_claims_status_index');
        $this->dropIndex('reimbursement_claims', 'reimbursement_claims_limit_check_index');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (!Schema::hasIndex($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                $t->index($columns, $name);
            });
        }
    }

    private function dropIndex(string $table, string $name, bool $unique = false): void
    {
        if (Schema::hasIndex($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($name, $unique) {
                $unique ? $t->dropUnique($name) : $t->dropIndex($name);
            });
        }
    }
};
