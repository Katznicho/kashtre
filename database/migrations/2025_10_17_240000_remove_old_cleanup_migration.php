<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the old problematic migration file that has the $this->command issue
        $oldMigrationPath = database_path('migrations/2025_10_17_221133_cleanup_duplicate_branch_item_prices.php');
        
        if (File::exists($oldMigrationPath)) {
            File::delete($oldMigrationPath);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse - we're just cleaning up old files
    }
};
