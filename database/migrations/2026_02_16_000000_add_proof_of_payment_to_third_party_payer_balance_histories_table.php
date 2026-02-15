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
        Schema::table('third_party_payer_balance_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('third_party_payer_balance_histories', 'proof_of_payment_path')) {
                $table->string('proof_of_payment_path')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('third_party_payer_balance_histories', function (Blueprint $table) {
            if (Schema::hasColumn('third_party_payer_balance_histories', 'proof_of_payment_path')) {
                $table->dropColumn('proof_of_payment_path');
            }
        });
    }
};
