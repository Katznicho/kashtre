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
        // First, clean up any duplicate records if they exist
        $duplicates = DB::select("
            SELECT business_id, COUNT(*) as count 
            FROM withdrawal_settings 
            WHERE deleted_at IS NULL 
            GROUP BY business_id 
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $duplicate) {
            // Keep the first record, delete the rest
            $records = DB::table('withdrawal_settings')
                ->where('business_id', $duplicate->business_id)
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();

            // Delete all but the first record
            if ($records->count() > 1) {
                $idsToDelete = $records->skip(1)->pluck('id');
                DB::table('withdrawal_settings')->whereIn('id', $idsToDelete)->delete();
            }
        }

        // Now add the unique constraint
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->unique('business_id', 'unique_business_withdrawal_setting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropUnique('unique_business_withdrawal_setting');
        });
    }
};