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
        // Force fix the package_tracking table structure
        $this->forceFixPackageTrackingStructure();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it fixes critical structure issues
    }

    /**
     * Force fix the package_tracking table structure
     */
    private function forceFixPackageTrackingStructure(): void
    {
        try {
            // First, check if the table exists and get its current structure
            $tableExists = DB::select("SHOW TABLES LIKE 'package_tracking'");
            
            if (empty($tableExists)) {
                // Table doesn't exist, nothing to fix
                return;
            }

            // Get current table structure
            $columns = DB::select("SHOW COLUMNS FROM package_tracking");
            $existingColumns = array_column($columns, 'Field');

            // Check if we need to fix the structure
            $needsFix = in_array('included_item_id', $existingColumns) || in_array('item_price', $existingColumns);

            if (!$needsFix) {
                // Structure is already correct
                return;
            }

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Drop problematic constraints first
            $this->dropProblematicConstraints();

            // Remove old columns if they exist
            if (in_array('included_item_id', $existingColumns)) {
                try {
                    DB::statement('ALTER TABLE package_tracking DROP COLUMN included_item_id');
                } catch (\Exception $e) {
                    // Column might be referenced by foreign keys, continue
                }
            }

            if (in_array('item_price', $existingColumns)) {
                try {
                    DB::statement('ALTER TABLE package_tracking DROP COLUMN item_price');
                } catch (\Exception $e) {
                    // Column might be referenced by foreign keys, continue
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Add the new unique constraint if it doesn't exist
            $this->addNewUniqueConstraint();

        } catch (\Exception $e) {
            // Log the error but don't fail the migration
            \Log::warning('Force fix package tracking structure failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Drop problematic constraints
     */
    private function dropProblematicConstraints(): void
    {
        try {
            // Get all constraints on the table
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'package_tracking'
            ");

            foreach ($constraints as $constraint) {
                if ($constraint->CONSTRAINT_NAME === 'unique_package_tracking_per_invoice') {
                    try {
                        DB::statement("ALTER TABLE package_tracking DROP INDEX {$constraint->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {
                        // Constraint might not exist or be referenced by foreign keys
                    }
                }
            }
        } catch (\Exception $e) {
            // Continue even if constraint dropping fails
        }
    }

    /**
     * Add new unique constraint
     */
    private function addNewUniqueConstraint(): void
    {
        try {
            // Check if the new constraint already exists
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'package_tracking' 
                AND CONSTRAINT_NAME = 'unique_package_tracking_per_invoice_new'
            ");

            if (empty($constraints)) {
                // Add the new unique constraint
                DB::statement("
                    ALTER TABLE package_tracking 
                    ADD UNIQUE KEY unique_package_tracking_per_invoice_new (invoice_id, package_item_id, client_id)
                ");
            }
        } catch (\Exception $e) {
            // Continue even if constraint addition fails
        }
    }
};