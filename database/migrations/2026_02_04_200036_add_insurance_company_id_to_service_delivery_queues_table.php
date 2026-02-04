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
        // Temporarily disable strict mode to avoid issues with timestamp defaults
        DB::statement('SET sql_mode = ""');
        
        // Use DB::statement to avoid MySQL strict mode issues
        DB::statement('ALTER TABLE service_delivery_queues ADD COLUMN insurance_company_id BIGINT UNSIGNED NULL AFTER client_id');
        DB::statement('ALTER TABLE service_delivery_queues ADD INDEX idx_insurance_company_id (insurance_company_id)');
        
        // Check if foreign key constraint can be added (insurance_companies table must exist)
        try {
            DB::statement('ALTER TABLE service_delivery_queues ADD CONSTRAINT fk_service_delivery_queues_insurance_company_id FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL');
        } catch (\Exception $e) {
            // If foreign key fails, just log it - the column and index are still created
            \Log::warning('Could not add foreign key constraint for insurance_company_id', ['error' => $e->getMessage()]);
        }
        
        // Re-enable strict mode
        DB::statement('SET sql_mode = "ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE service_delivery_queues DROP FOREIGN KEY fk_service_delivery_queues_insurance_company_id');
        DB::statement('ALTER TABLE service_delivery_queues DROP INDEX idx_insurance_company_id');
        DB::statement('ALTER TABLE service_delivery_queues DROP COLUMN insurance_company_id');
    }
};
