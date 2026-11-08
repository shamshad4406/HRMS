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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'hr', 'employee'])->default('employee')->after('email');
            $table->foreignId('personal_information_id')->nullable()->after('role')->constrained('personal_information')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['personal_information_id']);
            $table->dropColumn(['role', 'personal_information_id']);
        });
    }
};
