<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            // Add partially_done_at timestamp field
            $table->timestamp('partially_done_at')->nullable()->after('started_at');
        });

        // Update the status enum to include 'partially_done'
        DB::statement("ALTER TABLE service_delivery_queues MODIFY COLUMN status ENUM('pending', 'in_progress', 'partially_done', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_delivery_queues', function (Blueprint $table) {
            $table->dropColumn('partially_done_at');
        });

        // Revert the status enum
        DB::statement("ALTER TABLE service_delivery_queues MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
