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
        Log::info('Starting emergency fix for package tracking server database structure.');

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

        // Now fix the package_tracking table structure
        Schema::table('package_tracking', function (Blueprint $table) {
            // Disable foreign key checks to avoid issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Log::info('Foreign key checks disabled.');

            // Drop the old unique constraint if it exists
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if ($doctrineTable->hasIndex('unique_package_tracking_per_invoice')) {
                    $table->dropUnique('unique_package_tracking_per_invoice');
                    Log::info('Dropped old unique constraint: unique_package_tracking_per_invoice');
                }
            } catch (\Exception $e) {
                Log::warning("Could not drop old unique constraint: " . $e->getMessage());
            }

            // Remove the old fields that are no longer needed
            if (Schema::hasColumn('package_tracking', 'included_item_id')) {
                try {
                    // Drop foreign key first if it exists
                    $foreignKeys = DB::select(
                        DB::raw("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'package_tracking' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME LIKE '%included_item_id_foreign%'")
                    );
                    foreach ($foreignKeys as $fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                        Log::info("Dropped foreign key: {$fk->CONSTRAINT_NAME}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not drop foreign key for included_item_id: " . $e->getMessage());
                }
                $table->dropColumn('included_item_id');
                Log::info('Dropped included_item_id column.');
            } else {
                Log::info('included_item_id column does not exist, skipping drop.');
            }

            if (Schema::hasColumn('package_tracking', 'item_price')) {
                $table->dropColumn('item_price');
                Log::info('Dropped item_price column.');
            } else {
                Log::info('item_price column does not exist, skipping drop.');
            }

            // Add new unique constraint for the new structure
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if (!$doctrineTable->hasIndex('unique_package_tracking_per_invoice_new')) {
                    $table->unique([
                        'invoice_id',
                        'package_item_id',
                        'client_id'
                    ], 'unique_package_tracking_per_invoice_new');
                    Log::info('Added new unique constraint: unique_package_tracking_per_invoice_new');
                } else {
                    Log::info('New unique constraint already exists, skipping add.');
                }
            } catch (\Exception $e) {
                Log::warning("Could not add new unique constraint: " . $e->getMessage());
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info('Foreign key checks re-enabled.');
        });

        Log::info('Emergency fix for package tracking server database structure completed.');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('Reversing emergency fix for package tracking server database structure.');

        Schema::table('package_tracking', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Log::info('Foreign key checks disabled for rollback.');

            // Add back the old fields if they don't exist
            if (!Schema::hasColumn('package_tracking', 'included_item_id')) {
                $table->unsignedBigInteger('included_item_id')->after('package_item_id');
                $table->foreign('included_item_id')->references('id')->on('items')->onDelete('cascade');
                Log::info('Added included_item_id column and foreign key.');
            }

            if (!Schema::hasColumn('package_tracking', 'item_price')) {
                $table->decimal('item_price', 15, 2)->after('status');
                Log::info('Added item_price column.');
            }

            // Drop the new unique constraint if it exists
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if ($doctrineTable->hasIndex('unique_package_tracking_per_invoice_new')) {
                    $table->dropUnique('unique_package_tracking_per_invoice_new');
                    Log::info('Dropped new unique constraint: unique_package_tracking_per_invoice_new');
                }
            } catch (\Exception $e) {
                Log::warning("Could not drop new unique constraint: " . $e->getMessage());
            }

            // Add back the old unique constraint if it doesn't exist
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if (!$doctrineTable->hasIndex('unique_package_tracking_per_invoice')) {
                    $table->unique([
                        'invoice_id',
                        'package_item_id',
                        'included_item_id',
                        'client_id'
                    ], 'unique_package_tracking_per_invoice');
                    Log::info('Added back old unique constraint: unique_package_tracking_per_invoice');
                }
            } catch (\Exception $e) {
                Log::warning("Could not add back old unique constraint: " . $e->getMessage());
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info('Foreign key checks re-enabled for rollback.');
        });

        // Drop the package_tracking_items table if it exists
        if (Schema::hasTable('package_tracking_items')) {
            Schema::dropIfExists('package_tracking_items');
            Log::info('Dropped package_tracking_items table.');
        }

        Log::info('Emergency fix for package tracking server database structure reversed.');
    }
};
