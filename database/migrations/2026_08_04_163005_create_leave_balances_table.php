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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_information_id')->constrained('personal_information')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->decimal('total_days', 5, 2)->default(0);
            $table->decimal('used_days', 5, 2)->default(0);
            $table->decimal('remaining_days', 5, 2)->default(0);
            $table->decimal('carried_forward_days', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['personal_information_id', 'leave_type_id', 'year'], 'unique_employee_leave_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
