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
        // Update the enum values for payment_method column
        DB::statement("ALTER TABLE maturation_periods MODIFY COLUMN payment_method ENUM('insurance', 'credit_arrangement', 'mobile_money', 'v_card', 'p_card', 'bank_transfer', 'cash') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the old enum values
        DB::statement("ALTER TABLE maturation_periods MODIFY COLUMN payment_method ENUM('mtn', 'airtel', 'yo', 'cash', 'bank_transfer') NOT NULL");
    }
};