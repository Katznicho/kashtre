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
        // Temporarily disable strict mode
        DB::statement("SET sql_mode = ''");
        
        // Add extended_at column using raw SQL
        DB::statement("ALTER TABLE service_delivery_queues ADD COLUMN extended_at TIMESTAMP NULL AFTER partially_done_at");

        // Update the status enum to include 'not_done'
        DB::statement("ALTER TABLE service_delivery_queues MODIFY COLUMN status ENUM('pending', 'in_progress', 'partially_done', 'completed', 'cancelled', 'not_done') DEFAULT 'pending'");
        
        // Re-enable strict mode
        DB::statement("SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove extended_at column using raw SQL
        DB::statement("ALTER TABLE service_delivery_queues DROP COLUMN extended_at");

        // Revert the status enum
        DB::statement("ALTER TABLE service_delivery_queues MODIFY COLUMN status ENUM('pending', 'in_progress', 'partially_done', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
