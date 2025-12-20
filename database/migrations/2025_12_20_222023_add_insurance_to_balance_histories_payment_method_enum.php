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
        // Add 'insurance' to the payment_method enum in balance_histories table
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card', 'insurance') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'insurance' from the enum (revert to original)
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL");
    }
};
