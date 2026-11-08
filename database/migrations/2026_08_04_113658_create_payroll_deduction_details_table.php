<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_deduction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->enum('deduction_type', ['tax', 'pf', 'esi', 'leave', 'loan', 'advance', 'other']);
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->enum('status', ['applied', 'pending', 'waived'])->default('applied');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_deduction_details');
    }
};
