<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class FixPackageTrackingDatabase extends Command
{
    protected $signature = 'package:fix-database {--force : Force fix without confirmation}';
    protected $description = 'Emergency fix for package tracking database structure on server';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will modify the package_tracking table structure. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Starting emergency package tracking database fix...');

        try {
            DB::beginTransaction();

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $this->info('Foreign key checks disabled.');

            // Check if package_tracking_items table exists
            if (!Schema::hasTable('package_tracking_items')) {
                $this->info('Creating package_tracking_items table...');
                Schema::create('package_tracking_items', function ($table) {
                    $table->id();
                    $table->uuid('uuid')->unique()->index();
                    $table->foreignId('package_tracking_id')->constrained('package_tracking')->onDelete('cascade');
                    $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade');
                    $table->integer('total_quantity');
                    $table->integer('used_quantity')->default(0);
                    $table->integer('remaining_quantity');
                    $table->decimal('item_price', 15, 2);
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                    $table->unique(['package_tracking_id', 'included_item_id'], 'unique_package_item');
                });
                $this->info('✅ package_tracking_items table created.');
            } else {
                $this->info('✅ package_tracking_items table already exists.');
            }

            // Fix package_tracking table structure
            $this->info('Fixing package_tracking table structure...');

            // Drop old unique constraint if it exists
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if ($doctrineTable->hasIndex('unique_package_tracking_per_invoice')) {
                    DB::statement('ALTER TABLE package_tracking DROP INDEX unique_package_tracking_per_invoice');
                    $this->info('✅ Dropped old unique constraint.');
                }
            } catch (\Exception $e) {
                $this->warn("Could not drop old unique constraint: " . $e->getMessage());
            }

            // Remove old columns if they exist
            if (Schema::hasColumn('package_tracking', 'included_item_id')) {
                // Drop foreign key first
                try {
                    $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'package_tracking' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME LIKE '%included_item_id_foreign%'");
                    foreach ($foreignKeys as $fk) {
                        DB::statement("ALTER TABLE package_tracking DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                        $this->info("Dropped foreign key: {$fk->CONSTRAINT_NAME}");
                    }
                } catch (\Exception $e) {
                    $this->warn("Could not drop foreign key: " . $e->getMessage());
                }
                
                DB::statement('ALTER TABLE package_tracking DROP COLUMN included_item_id');
                $this->info('✅ Dropped included_item_id column.');
            } else {
                $this->info('✅ included_item_id column does not exist.');
            }

            if (Schema::hasColumn('package_tracking', 'item_price')) {
                DB::statement('ALTER TABLE package_tracking DROP COLUMN item_price');
                $this->info('✅ Dropped item_price column.');
            } else {
                $this->info('✅ item_price column does not exist.');
            }

            // Add new unique constraint
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('package_tracking');
                if (!$doctrineTable->hasIndex('unique_package_tracking_per_invoice_new')) {
                    DB::statement('ALTER TABLE package_tracking ADD UNIQUE unique_package_tracking_per_invoice_new (invoice_id, package_item_id, client_id)');
                    $this->info('✅ Added new unique constraint.');
                } else {
                    $this->info('✅ New unique constraint already exists.');
                }
            } catch (\Exception $e) {
                $this->warn("Could not add new unique constraint: " . $e->getMessage());
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->info('Foreign key checks re-enabled.');

            DB::commit();

            $this->info('🎉 Package tracking database structure fixed successfully!');
            Log::info('Package tracking database structure fixed successfully via command');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Failed to fix package tracking database structure: ' . $e->getMessage());
            Log::error('Failed to fix package tracking database structure', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
