<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default operating country/currency for businesses: United States + USD.
     */
    public function up(): void
    {
        if (! Schema::hasTable('businesses')) {
            return;
        }

        $now = now();

        if (Schema::hasTable('currencies')) {
            $usdCurrencyId = DB::table('currencies')->where('code', 'USD')->value('id');
            if (! $usdCurrencyId) {
                DB::table('currencies')->insert([
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'symbol' => '$',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $usdCurrencyId = DB::table('currencies')->where('code', 'USD')->value('id');
            }
        } else {
            $usdCurrencyId = null;
        }

        $usCountryId = null;
        if (Schema::hasTable('countries')) {
            $usCountryId = DB::table('countries')->where('iso_code', 'US')->value('id');
            if (! $usCountryId && $usdCurrencyId) {
                DB::table('countries')->insert([
                    'name' => 'United States',
                    'iso_code' => 'US',
                    'currency_id' => $usdCurrencyId,
                    'currency_code' => 'USD',
                    'exchange_rate_to_usd' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $usCountryId = DB::table('countries')->where('iso_code', 'US')->value('id');
            }
        }

        $update = [
            'currency_code' => 'USD',
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('businesses', 'exchange_rate_to_usd')) {
            $update['exchange_rate_to_usd'] = null;
        }
        if ($usCountryId && Schema::hasColumn('businesses', 'country_id')) {
            $update['country_id'] = $usCountryId;
        }

        DB::table('businesses')->update($update);

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('businesses', 'currency_code')) {
            try {
                DB::statement("ALTER TABLE `businesses` MODIFY `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD'");
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `businesses` MODIFY `currency_code` VARCHAR(10) NULL DEFAULT 'USD'");
                } catch (\Throwable $e2) {
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('businesses')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true) && Schema::hasColumn('businesses', 'currency_code')) {
            try {
                DB::statement("ALTER TABLE `businesses` MODIFY `currency_code` VARCHAR(10) NOT NULL DEFAULT 'UGX'");
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `businesses` MODIFY `currency_code` VARCHAR(10) NULL DEFAULT 'UGX'");
                } catch (\Throwable $e2) {
                }
            }
        }
    }
};
