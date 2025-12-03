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
        // Only add payment_status if it doesn't exist
        if (!Schema::hasColumn('business_balance_histories', 'payment_status')) {
            Schema::table('business_balance_histories', function (Blueprint $table) {
                // Add payment_status enum with default 'paid'
                $table->enum('payment_status', ['paid', 'pending_payment'])->default('paid')->after('metadata');
            });
        }
        
        // Only add payment_method if it doesn't exist
        if (!Schema::hasColumn('business_balance_histories', 'payment_method')) {
            Schema::table('business_balance_histories', function (Blueprint $table) {
                // Add payment_method enum with default 'mobile_money'
                $table->enum('payment_method', ['account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card'])->default('mobile_money')->after('payment_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_balance_histories', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method']);
        });
    }
};
