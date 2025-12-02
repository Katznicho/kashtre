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
        Schema::table('balance_histories', function (Blueprint $table) {
            // Add payment_status enum with default 'pending_payment'
            $table->enum('payment_status', ['paid', 'pending_payment'])->default('pending_payment')->after('payment_reference');
        });

        // Update payment_method to enum with specified values
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_histories', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        // Revert payment_method back to string
        DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method VARCHAR(255) NULL");
    }
};
