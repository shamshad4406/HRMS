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
        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque', 'other']);
            $table->string('payment_reference')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('notes')->nullable();
            $table->foreignId('processed_by')->nullable();
            $table->timestamps();
        });

        Schema::table('payroll_payments', function (Blueprint $table) {
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
};
