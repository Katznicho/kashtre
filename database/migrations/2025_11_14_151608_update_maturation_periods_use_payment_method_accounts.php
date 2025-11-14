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
        Schema::table('maturation_periods', function (Blueprint $table) {
            // Drop the old account_id foreign key if it exists
            if (Schema::hasColumn('maturation_periods', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
            
            // Drop branch_id if it exists
            if (Schema::hasColumn('maturation_periods', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            
            // Add payment_method_account_id instead
            if (!Schema::hasColumn('maturation_periods', 'payment_method_account_id')) {
                $table->foreignId('payment_method_account_id')->nullable()->after('business_id')
                    ->constrained('payment_method_accounts')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maturation_periods', function (Blueprint $table) {
            // Drop payment_method_account_id
            if (Schema::hasColumn('maturation_periods', 'payment_method_account_id')) {
                $table->dropForeign(['payment_method_account_id']);
                $table->dropColumn('payment_method_account_id');
            }
            
            // Restore account_id if needed
            if (!Schema::hasColumn('maturation_periods', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('business_id')
                    ->constrained('money_accounts')->onDelete('set null');
            }
        });
    }
};
