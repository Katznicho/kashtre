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
        // First, we need to drop the foreign key constraint temporarily
        // because it references the unique index we're about to modify
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
        });

        // Drop the existing unique constraint that only includes business_id
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropUnique('unique_business_withdrawal_setting');
        });

        // Add the new unique constraint that includes both business_id and withdrawal_type
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->unique(['business_id', 'withdrawal_type'], 'unique_business_withdrawal_type');
        });

        // Re-add the foreign key constraint
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint temporarily
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
        });

        // Drop the new constraint
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropUnique('unique_business_withdrawal_type');
        });

        // Restore the old constraint
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->unique('business_id', 'unique_business_withdrawal_setting');
        });

        // Re-add the foreign key constraint
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }
};