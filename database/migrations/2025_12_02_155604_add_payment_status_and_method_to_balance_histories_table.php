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
        // Only add payment_status if it doesn't exist
        if (!Schema::hasColumn('balance_histories', 'payment_status')) {
            Schema::table('balance_histories', function (Blueprint $table) {
                // Add payment_status enum with default 'pending_payment'
                $table->enum('payment_status', ['paid', 'pending_payment'])->default('pending_payment')->after('payment_reference');
            });
        }

        // First, normalize existing payment_method values
        $validMethods = ['account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card'];
        
        // Map common variations to valid ENUM values
        $mapping = [
            'account balance' => 'account_balance',
            'Account Balance' => 'account_balance',
            'mobile money' => 'mobile_money',
            'Mobile Money' => 'mobile_money',
            'mobilemoney' => 'mobile_money',
            'bank transfer' => 'bank_transfer',
            'Bank Transfer' => 'bank_transfer',
            'banktransfer' => 'bank_transfer',
            'v card' => 'v_card',
            'V Card' => 'v_card',
            'vcard' => 'v_card',
            'p card' => 'p_card',
            'P Card' => 'p_card',
            'pcard' => 'p_card',
            'cash' => 'account_balance',
            'Cash' => 'account_balance',
        ];
        
        // Update mapped values
        foreach ($mapping as $oldValue => $newValue) {
            DB::table('balance_histories')
                ->where('payment_method', $oldValue)
                ->update(['payment_method' => $newValue]);
        }
        
        // Set all other non-null values that don't match valid methods to NULL
        // This handles any unexpected values that can't be mapped
        DB::table('balance_histories')
            ->whereNotNull('payment_method')
            ->whereNotIn('payment_method', $validMethods)
            ->update(['payment_method' => null]);

        // Now update payment_method to enum with specified values (only if it's not already an ENUM)
        $columnInfo = DB::select("SHOW COLUMNS FROM balance_histories WHERE Field = 'payment_method'");
        if (!empty($columnInfo) && stripos($columnInfo[0]->Type, 'enum') === false) {
            DB::statement("ALTER TABLE balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card') NULL");
        }
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
