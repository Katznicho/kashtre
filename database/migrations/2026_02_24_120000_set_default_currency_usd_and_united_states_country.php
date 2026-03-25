<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies') || ! Schema::hasTable('countries')) {
            return;
        }

        $now = now();

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

        if (! DB::table('countries')->where('iso_code', 'US')->exists()) {
            DB::table('countries')->insert([
                'name' => 'United States',
                'iso_code' => 'US',
                'currency_id' => $usdCurrencyId,
                'currency_code' => 'USD',
                'exchange_rate_to_usd' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $tables = [
            'businesses' => 'currency_code',
            'insurance_companies' => 'currency_code',
            'invoices' => 'currency',
        ];

        foreach ($tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(10) NOT NULL DEFAULT 'USD'");
            } catch (\Throwable $e) {
                // Column may differ (nullable); try with NULL default
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(10) NULL DEFAULT 'USD'");
                } catch (\Throwable $e2) {
                    // Leave as-is if alter fails
                }
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $tables = [
            'businesses' => 'currency_code',
            'insurance_companies' => 'currency_code',
            'invoices' => 'currency',
        ];

        foreach ($tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(10) NOT NULL DEFAULT 'UGX'");
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(10) NULL DEFAULT 'UGX'");
                } catch (\Throwable $e2) {
                }
            }
        }
    }
};
