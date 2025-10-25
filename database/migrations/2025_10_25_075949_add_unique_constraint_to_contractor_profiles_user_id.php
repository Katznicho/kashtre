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
        Schema::table('contractor_profiles', function (Blueprint $table) {
            // Add unique constraint on user_id to prevent multiple contractor profiles per user
            $table->unique('user_id', 'contractor_profiles_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_profiles', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('contractor_profiles_user_id_unique');
        });
    }
};
