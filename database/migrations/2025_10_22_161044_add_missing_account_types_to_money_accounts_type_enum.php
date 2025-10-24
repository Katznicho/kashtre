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
        // Add new account types to the ENUM
        DB::statement("ALTER TABLE money_accounts MODIFY COLUMN type ENUM(
            'client_account',
            'package_suspense_account',
            'general_suspense_account',
            'kashtre_suspense_account',
            'business_account',
            'contractor_account',
            'kashtre_account',
            'client_suspense_account',
            'mobile_money_account'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new account types from the ENUM
        DB::statement("ALTER TABLE money_accounts MODIFY COLUMN type ENUM(
            'client_account',
            'package_suspense_account',
            'general_suspense_account',
            'kashtre_suspense_account',
            'business_account',
            'contractor_account',
            'kashtre_account'
        )");
    }
};
