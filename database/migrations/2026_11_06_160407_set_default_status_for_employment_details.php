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
        // Fix any employment details with null status
        DB::table('employment_details')
            ->whereNull('status')
            ->update(['status' => 'active']);

        // Sync user.is_active with employment_detail.status
        DB::statement('
            UPDATE users u
            INNER JOIN employment_details ed ON u.personal_information_id = ed.personal_information_id
            SET u.is_active = (ed.status = "active")
            WHERE u.role = "employee"
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is data-fixing, so rollback doesn't make sense
        // We'll leave the data as is
    }
};
