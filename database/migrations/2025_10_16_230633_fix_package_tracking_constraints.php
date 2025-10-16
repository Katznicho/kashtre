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
        // First, check if the old unique constraint exists and drop it safely
        $this->dropOldUniqueConstraintIfExists();
        
        // Check if old columns exist and drop them safely
        $this->dropOldColumnsIfExist();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old columns if they don't exist
        Schema::table('package_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('package_tracking', 'included_item_id')) {
                $table->unsignedBigInteger('included_item_id')->nullable();
            }
            if (!Schema::hasColumn('package_tracking', 'item_price')) {
                $table->decimal('item_price', 15, 2)->nullable();
            }
        });

        // Add back the old unique constraint
        Schema::table('package_tracking', function (Blueprint $table) {
            $table->unique([
                'invoice_id', 
                'package_item_id', 
                'included_item_id', 
                'client_id'
            ], 'unique_package_tracking_per_invoice');
        });
    }

    /**
     * Drop the old unique constraint if it exists
     */
    private function dropOldUniqueConstraintIfExists(): void
    {
        try {
            // Check if the constraint exists
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'package_tracking' 
                AND CONSTRAINT_NAME = 'unique_package_tracking_per_invoice'
            ");

            if (!empty($constraints)) {
                // Drop the constraint
                DB::statement('ALTER TABLE package_tracking DROP INDEX unique_package_tracking_per_invoice');
            }
        } catch (\Exception $e) {
            // Constraint doesn't exist or already dropped, continue
        }
    }

    /**
     * Drop old columns if they exist
     */
    private function dropOldColumnsIfExist(): void
    {
        try {
            // Check if included_item_id column exists
            $columns = DB::select("
                SELECT COLUMN_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'package_tracking' 
                AND COLUMN_NAME IN ('included_item_id', 'item_price')
            ");

            $existingColumns = array_column($columns, 'COLUMN_NAME');

            if (in_array('included_item_id', $existingColumns)) {
                Schema::table('package_tracking', function (Blueprint $table) {
                    $table->dropColumn('included_item_id');
                });
            }

            if (in_array('item_price', $existingColumns)) {
                Schema::table('package_tracking', function (Blueprint $table) {
                    $table->dropColumn('item_price');
                });
            }
        } catch (\Exception $e) {
            // Columns don't exist or already dropped, continue
        }
    }
};