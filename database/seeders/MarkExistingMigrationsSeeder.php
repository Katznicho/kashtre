<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarkExistingMigrationsSeeder extends Seeder
{
    /**
     * Run the database seeds to mark existing migrations as run.
     */
    public function run(): void
    {
        $existingMigrations = [
            '2013_06_23_132952_create_businesses_table',
            '2025_10_12_000000_create_users_table',
            '2025_10_12_200000_add_two_factor_columns_to_users_table',
            '2025_10_23_143816_create_transactions_table',
            '2025_11_23_203441_create_activity_logs_table'
        ];

        foreach ($existingMigrations as $migration) {
            try {
                DB::statement('INSERT IGNORE INTO migrations (migration, batch) VALUES (?, 1)', [$migration]);
                echo "Marked {$migration} as run\n";
            } catch (\Exception $e) {
                echo "Failed to mark {$migration}: {$e->getMessage()}\n";
            }
        }
    }
}