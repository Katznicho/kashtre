<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Log::info('Starting simple fix for package tracking database structure.');

        // First, check if the package_tracking_items table exists
        if (!Schema::hasTable('package_tracking_items')) {
            Log::info('Creating package_tracking_items table...');
            Schema::create('package_tracking_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->index();
                $table->foreignId('package_tracking_id')->constrained('package_tracking')->onDelete('cascade');
                $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade');
                $table->integer('total_quantity'); // Total quantity available for this included item
                $table->integer('used_quantity')->default(0); // Quantity already used
                $table->integer('remaining_quantity'); // Remaining quantity
                $table->decimal('item_price', 15, 2); // Individual item price
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Ensure no duplicate tracking items for the same package and included item
                $table->unique(['package_tracking_id', 'included_item_id'], 'unique_package_item');
            });
            Log::info('package_tracking_items table created successfully.');
        } else {
            Log::info('package_tracking_items table already exists.');
        }

        // Now fix the package_tracking table structure using direct SQL
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Log::info('Foreign key checks disabled.');

            // Drop old unique constraint if it exists
            try {
                DB::statement('ALTER TABLE package_tracking DROP INDEX unique_package_tracking_per_invoice');
                Log::info('Dropped old unique constraint: unique_package_tracking_per_invoice');
            } catch (\Exception $e) {
                Log::info('Old unique constraint does not exist or already dropped.');
            }

            // Remove old columns if they exist
            if (Schema::hasColumn('package_tracking', 'included_item_id')) {
                // Try to drop foreign key first
                try {
                    DB::statement('ALTER TABLE package_tracking DROP FOREIGN KEY package_tracking_included_item_id_foreign');
                } catch (\Exception $e) {
                    Log::info('Foreign key for included_item_id does not exist or already dropped.');
                }
                
                DB::statement('ALTER TABLE package_tracking DROP COLUMN included_item_id');
                Log::info('Dropped included_item_id column.');
            } else {
                Log::info('included_item_id column does not exist, skipping drop.');
            }

            if (Schema::hasColumn('package_tracking', 'item_price')) {
                DB::statement('ALTER TABLE package_tracking DROP COLUMN item_price');
                Log::info('Dropped item_price column.');
            } else {
                Log::info('item_price column does not exist, skipping drop.');
            }

            // Add new unique constraint
            try {
                DB::statement('ALTER TABLE package_tracking ADD UNIQUE unique_package_tracking_per_invoice_new (invoice_id, package_item_id, client_id)');
                Log::info('Added new unique constraint: unique_package_tracking_per_invoice_new');
            } catch (\Exception $e) {
                Log::info('New unique constraint already exists or could not be added: ' . $e->getMessage());
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info('Foreign key checks re-enabled.');

        } catch (\Exception $e) {
            Log::error('Error in simple package tracking fix: ' . $e->getMessage());
            throw $e;
        }

        Log::info('Simple fix for package tracking database structure completed.');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('Reversing simple fix for package tracking database structure.');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Log::info('Foreign key checks disabled for rollback.');

            // Add back the old fields if they don't exist
            if (!Schema::hasColumn('package_tracking', 'included_item_id')) {
                DB::statement('ALTER TABLE package_tracking ADD COLUMN included_item_id BIGINT UNSIGNED AFTER package_item_id');
                DB::statement('ALTER TABLE package_tracking ADD FOREIGN KEY (included_item_id) REFERENCES items(id) ON DELETE CASCADE');
                Log::info('Added included_item_id column and foreign key.');
            }

            if (!Schema::hasColumn('package_tracking', 'item_price')) {
                DB::statement('ALTER TABLE package_tracking ADD COLUMN item_price DECIMAL(15,2) AFTER status');
                Log::info('Added item_price column.');
            }

            // Drop the new unique constraint if it exists
            try {
                DB::statement('ALTER TABLE package_tracking DROP INDEX unique_package_tracking_per_invoice_new');
                Log::info('Dropped new unique constraint: unique_package_tracking_per_invoice_new');
            } catch (\Exception $e) {
                Log::info('New unique constraint does not exist or already dropped.');
            }

            // Add back the old unique constraint if it doesn't exist
            try {
                DB::statement('ALTER TABLE package_tracking ADD UNIQUE unique_package_tracking_per_invoice (invoice_id, package_item_id, included_item_id, client_id)');
                Log::info('Added back old unique constraint: unique_package_tracking_per_invoice');
            } catch (\Exception $e) {
                Log::info('Old unique constraint already exists or could not be added: ' . $e->getMessage());
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info('Foreign key checks re-enabled for rollback.');
        } catch (\Exception $e) {
            Log::error('Error in simple package tracking rollback: ' . $e->getMessage());
            throw $e;
        }

        // Drop the package_tracking_items table if it exists
        if (Schema::hasTable('package_tracking_items')) {
            Schema::dropIfExists('package_tracking_items');
            Log::info('Dropped package_tracking_items table.');
        }

        Log::info('Simple fix for package tracking database structure reversed.');
    }
};
