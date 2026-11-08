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
            $table->string('activation_token')->nullable()->after('remember_token');
            $table->timestamp('activation_expires_at')->nullable()->after('activation_token');
            $table->boolean('is_active')->default(false)->after('activation_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['activation_token', 'activation_expires_at', 'is_active']);
        });
    }
};