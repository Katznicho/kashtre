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
        // Remove branch_id from payment_method_accounts using raw SQL
        try {
            // Check if column exists and drop unique constraint first
            $columns = DB::select("SHOW COLUMNS FROM payment_method_accounts LIKE 'branch_id'");
            if (!empty($columns)) {
                // Drop unique constraint if it includes branch_id
                try {
                    DB::statement('ALTER TABLE payment_method_accounts DROP INDEX unique_payment_method_account');
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
                
                // Drop foreign key if exists
                $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payment_method_accounts' AND COLUMN_NAME = 'branch_id' AND CONSTRAINT_NAME != 'PRIMARY'");
                foreach ($fks as $fk) {
                    try {
                        DB::statement("ALTER TABLE payment_method_accounts DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {
                        // Continue
                    }
                }
                
                // Drop column
                DB::statement('ALTER TABLE payment_method_accounts DROP COLUMN branch_id');
                
                // Recreate unique constraint without branch_id
                DB::statement('ALTER TABLE payment_method_accounts ADD UNIQUE unique_payment_method_account (business_id, payment_method, provider)');
            }
        } catch (\Exception $e) {
            // Column doesn't exist, continue
        }
        
        // Remove branch_id from maturation_periods
        try {
            $columns = DB::select("SHOW COLUMNS FROM maturation_periods LIKE 'branch_id'");
            if (!empty($columns)) {
                $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'maturation_periods' AND COLUMN_NAME = 'branch_id' AND CONSTRAINT_NAME != 'PRIMARY'");
                foreach ($fks as $fk) {
                    try {
                        DB::statement("ALTER TABLE maturation_periods DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {
                        // Continue
                    }
                }
                DB::statement('ALTER TABLE maturation_periods DROP COLUMN branch_id');
            }
        } catch (\Exception $e) {
            // Column doesn't exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add branch_id back if needed - this is for rollback purposes
    }
};
