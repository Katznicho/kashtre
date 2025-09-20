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
        Schema::table('package_tracking', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate package tracking records
            // A client can only have one tracking record per invoice per package item per included item
            $table->unique([
                'invoice_id', 
                'package_item_id', 
                'included_item_id', 
                'client_id'
            ], 'unique_package_tracking_per_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_tracking', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('unique_package_tracking_per_invoice');
        });
    }
};
