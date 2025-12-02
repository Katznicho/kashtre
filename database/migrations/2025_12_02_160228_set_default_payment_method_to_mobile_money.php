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
        // Set default payment_method to 'mobile_money' for balance_histories
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') DEFAULT 'mobile_money'");
        
        // Set default payment_method to 'mobile_money' for business_balance_histories
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') DEFAULT 'mobile_money'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default from balance_histories
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL");
        
        // Remove default from business_balance_histories
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL");
    }
};
