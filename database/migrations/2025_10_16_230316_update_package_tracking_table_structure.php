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

        // Dropping included_item_id can leave duplicate (invoice_id, package_item_id, client_id) rows.
        $this->deduplicatePackageTrackingBeforeUnique();

        if ($this->packageTrackingHasUniqueIndex('unique_package_tracking_per_invoice_new')) {
            return;
        }

        // Add new unique constraint for the new structure
        Schema::table('package_tracking', function (Blueprint $table) {
            $table->unique([
                'invoice_id',
                'package_item_id',
                'client_id',
            ], 'unique_package_tracking_per_invoice_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_tracking', function (Blueprint $table) {
            // Add back the old fields
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('item_price', 15, 2);
            
            // Drop the new unique constraint
            $table->dropUnique('unique_package_tracking_per_invoice_new');
            
            // Add back the old unique constraint
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

    private function packageTrackingHasUniqueIndex(string $indexName): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = ?
             AND INDEX_NAME = ?
             LIMIT 1',
            ['package_tracking', $indexName]
        );

        return ! empty($rows);
    }

    /**
     * Collapse duplicate package_tracking rows so (invoice_id, package_item_id, client_id) is unique.
     * Keeps the lowest id per group, merges quantities and child rows, then deletes extras.
     */
    private function deduplicatePackageTrackingBeforeUnique(): void
    {
        $groups = DB::select(
            'SELECT invoice_id, package_item_id, client_id, MIN(id) AS keep_id, COUNT(*) AS cnt
             FROM package_tracking
             GROUP BY invoice_id, package_item_id, client_id
             HAVING cnt > 1'
        );

        foreach ($groups as $g) {
            $keepId = (int) $g->keep_id;
            $ids = DB::table('package_tracking')
                ->where('invoice_id', $g->invoice_id)
                ->where('package_item_id', $g->package_item_id)
                ->where('client_id', $g->client_id)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $duplicateIds = array_values(array_filter($ids, fn ($id) => (int) $id !== $keepId));

            $totals = DB::table('package_tracking')
                ->whereIn('id', $ids)
                ->selectRaw('COALESCE(SUM(total_quantity), 0) AS total_quantity, COALESCE(SUM(used_quantity), 0) AS used_quantity, COALESCE(SUM(remaining_quantity), 0) AS remaining_quantity')
                ->first();

            DB::table('package_tracking')->where('id', $keepId)->update([
                'total_quantity' => (int) $totals->total_quantity,
                'used_quantity' => (int) $totals->used_quantity,
                'remaining_quantity' => (int) $totals->remaining_quantity,
                'updated_at' => now(),
            ]);

            foreach ($duplicateIds as $dupId) {
                $this->reassignPackageTrackingItems((int) $dupId, $keepId);

                if (Schema::hasTable('package_sales')) {
                    DB::table('package_sales')
                        ->where('package_tracking_id', $dupId)
                        ->update(['package_tracking_id' => $keepId]);
                }

                DB::table('package_tracking')->where('id', $dupId)->delete();
            }
        }
    }

    private function reassignPackageTrackingItems(int $fromId, int $toId): void
    {
        if (! Schema::hasTable('package_tracking_items')) {
            return;
        }

        $items = DB::table('package_tracking_items')
            ->where('package_tracking_id', $fromId)
            ->orderBy('id')
            ->get();

        foreach ($items as $item) {
            $existing = DB::table('package_tracking_items')
                ->where('package_tracking_id', $toId)
                ->where('included_item_id', $item->included_item_id)
                ->first();

            if ($existing) {
                DB::table('package_tracking_items')->where('id', $existing->id)->update([
                    'total_quantity' => (int) $existing->total_quantity + (int) $item->total_quantity,
                    'used_quantity' => (int) $existing->used_quantity + (int) $item->used_quantity,
                    'remaining_quantity' => (int) $existing->remaining_quantity + (int) $item->remaining_quantity,
                    'updated_at' => now(),
                ]);
                DB::table('package_tracking_items')->where('id', $item->id)->delete();
            } else {
                DB::table('package_tracking_items')->where('id', $item->id)->update([
                    'package_tracking_id' => $toId,
                    'updated_at' => now(),
                ]);
            }
        }
    }
};