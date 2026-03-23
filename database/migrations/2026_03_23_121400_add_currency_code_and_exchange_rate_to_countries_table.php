<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'currency_code')) {
                $table->string('currency_code', 10)->nullable()->after('iso_code');
            }

            if (!Schema::hasColumn('countries', 'exchange_rate_to_usd')) {
                $table->decimal('exchange_rate_to_usd', 18, 6)->default(1)->after('currency_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'exchange_rate_to_usd')) {
                $table->dropColumn('exchange_rate_to_usd');
            }
            if (Schema::hasColumn('countries', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
        });
    }
};

