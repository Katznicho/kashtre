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
        Schema::table('third_party_payers', function (Blueprint $table) {
            $table->dropColumn('current_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('third_party_payers', function (Blueprint $table) {
            $table->decimal('current_balance', 15, 2)->default(0.00)->comment('Current outstanding balance')->after('credit_limit');
        });
    }
};
