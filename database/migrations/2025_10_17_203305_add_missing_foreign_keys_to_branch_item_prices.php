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
        // Check if the foreign key constraint already exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'branch_item_prices' 
            AND COLUMN_NAME = 'item_id' 
            AND CONSTRAINT_NAME LIKE '%_foreign'
        ");
        
        $constraintExists = !empty($foreignKeys);
        
        if (!$constraintExists) {
            Schema::table('branch_item_prices', function (Blueprint $table) {
                // Add missing foreign key constraint for item_id -> items.id
                // This is the most critical one that was missing and caused the corruption
                // business_id and branch_id foreign keys already exist
                $table->foreign('item_id')
                      ->references('id')
                      ->on('items')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_item_prices', function (Blueprint $table) {
            // Drop the foreign key constraint for item_id
            $table->dropForeign(['item_id']);
        });
    }
};