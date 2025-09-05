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
        // Add 'yo' to the provider ENUM
        DB::statement("ALTER TABLE transactions MODIFY COLUMN provider ENUM('mtn', 'airtel', 'yo') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'yo' from the provider ENUM (revert to original)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN provider ENUM('mtn', 'airtel') NOT NULL");
    }
};
