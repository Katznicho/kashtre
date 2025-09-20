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
        // Add 'package' to the type enum in business_balance_histories table
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN type ENUM(
            'credit',
            'debit',
            'package'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'package' from the type enum in business_balance_histories table
        DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN type ENUM(
            'credit',
            'debit'
        )");
    }
};
