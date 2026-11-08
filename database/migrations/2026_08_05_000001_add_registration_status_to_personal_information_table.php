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
        Schema::table('personal_information', function (Blueprint $table) {
            $table->enum('registration_status', ['pending', 'completed', 'pending_activation'])->default('pending')->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->dropColumn('registration_status');
        });
    }
};