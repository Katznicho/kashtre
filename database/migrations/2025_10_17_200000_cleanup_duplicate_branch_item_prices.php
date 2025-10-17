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
        // Clean up duplicate branch item prices before adding unique constraint
        \Log::info('Cleaning up duplicate branch item prices...');
        
        // Find and remove duplicates, keeping the most recent one
        $duplicates = DB::table('branch_item_prices')
            ->select('business_id', 'item_id', 'branch_id', DB::raw('COUNT(*) as count'))
            ->groupBy('business_id', 'item_id', 'branch_id')
            ->having('count', '>', 1)
            ->get();
        
        \Log::info("Found " . $duplicates->count() . " duplicate combinations");
        
        foreach ($duplicates as $duplicate) {
            \Log::info("Processing duplicates for business_id={$duplicate->business_id}, item_id={$duplicate->item_id}, branch_id={$duplicate->branch_id}");
            
            // Get all records for this combination
            $records = DB::table('branch_item_prices')
                ->where('business_id', $duplicate->business_id)
                ->where('item_id', $duplicate->item_id)
                ->where('branch_id', $duplicate->branch_id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Keep the first (most recent) record, delete the rest
            $keepRecord = $records->first();
            $deleteRecords = $records->skip(1);
            
            \Log::info("Keeping record ID {$keepRecord->id} (created: {$keepRecord->created_at}), deleting " . $deleteRecords->count() . " duplicates");
            
            foreach ($deleteRecords as $deleteRecord) {
                DB::table('branch_item_prices')->where('id', $deleteRecord->id)->delete();
                \Log::info("Deleted duplicate record ID {$deleteRecord->id}");
            }
        }
        
        \Log::info('Duplicate cleanup completed!');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as we're deleting data
        \Log::warning('This migration cannot be reversed - duplicate data has been permanently removed');
    }
};
