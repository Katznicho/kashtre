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
        // Remove the migration record from the migrations table
        // This is needed because the old migration file was deleted but the record still exists
        DB::table('migrations')
            ->where('migration', '2025_10_17_221133_cleanup_duplicate_branch_item_prices')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse - we're just cleaning up database records
    }
};
