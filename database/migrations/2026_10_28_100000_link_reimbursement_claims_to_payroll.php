<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_claims', function (Blueprint $table) {
            // The payroll that pays this claim (null = paid directly / not yet included)
            $table->foreignId('payroll_id')->nullable()->after('personal_information_id')
                ->constrained('payrolls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_claims', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payroll_id');
        });
    }
};
