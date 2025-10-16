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
            // Drop the old unique constraint that included included_item_id
            $table->dropUnique('unique_package_tracking_per_invoice');
            
            // Remove the old fields that are no longer needed with the new structure
            $table->dropColumn(['included_item_id', 'item_price']);
            
            // Add new unique constraint for the new structure
            $table->unique([
                'invoice_id', 
                'package_item_id', 
                'client_id'
            ], 'unique_package_tracking_per_invoice_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_tracking', function (Blueprint $table) {
            // Add back the old fields
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('item_price', 15, 2);
            
            // Drop the new unique constraint
            $table->dropUnique('unique_package_tracking_per_invoice_new');
            
            // Add back the old unique constraint
            $table->unique([
                'invoice_id', 
                'package_item_id', 
                'included_item_id', 
                'client_id'
            ], 'unique_package_tracking_per_invoice');
        });
    }
};