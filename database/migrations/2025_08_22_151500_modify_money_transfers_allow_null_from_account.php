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
        Schema::table('money_transfers', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['from_account_id']);
            
            // Modify the column to allow null values
            $table->foreignId('from_account_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable
            $table->foreign('from_account_id')->references('id')->on('money_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('money_transfers', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['from_account_id']);
            
            // Modify the column back to not nullable
            $table->foreignId('from_account_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint without nullable
            $table->foreign('from_account_id')->references('id')->on('money_accounts')->onDelete('cascade');
        });
    }
};
