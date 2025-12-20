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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_balance', 15, 2)->default(0.00)->after('status')->comment('Total balance for cashier/third-party payer users');
            $table->decimal('current_balance', 15, 2)->default(0.00)->after('total_balance')->comment('Current available balance for cashier/third-party payer users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_balance', 'current_balance']);
        });
    }
};
