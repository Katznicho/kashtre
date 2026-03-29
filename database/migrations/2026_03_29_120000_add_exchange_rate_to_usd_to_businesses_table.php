<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-business override for USD exchange rate (optional; falls back to country default).
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'exchange_rate_to_usd')) {
                $table->decimal('exchange_rate_to_usd', 18, 6)->nullable()->after('currency_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'exchange_rate_to_usd')) {
                $table->dropColumn('exchange_rate_to_usd');
            }
        });
    }
};
