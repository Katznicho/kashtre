<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branch_item_prices', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate branch prices for same item and branch
            $table->unique(['business_id', 'item_id', 'branch_id'], 'branch_item_prices_business_item_branch_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_item_prices', function (Blueprint $table) {
            $table->dropUnique('branch_item_prices_business_item_branch_unique');
        });
    }
};