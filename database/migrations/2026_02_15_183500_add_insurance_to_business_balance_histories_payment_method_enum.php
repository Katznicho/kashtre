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
        // Add 'insurance' and 'credit_arrangement' to the payment_method enum in business_balance_histories table
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card', 'insurance', 'credit_arrangement') NULL DEFAULT 'mobile_money'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'insurance' and 'credit_arrangement' from the enum (revert to original)
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL DEFAULT 'mobile_money'");
    }
};
