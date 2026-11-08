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
        Schema::table('employment_details', function (Blueprint $table) {
            // Change reporting_manager from string to foreign key reference
            $table->dropColumn('reporting_manager');
            $table->foreignId('reporting_manager_id')->nullable()->after('joining_date')->constrained('personal_information')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropColumn('reporting_manager_id');
            $table->string('reporting_manager')->nullable()->after('joining_date');
        });
    }
};
