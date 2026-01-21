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
        // Get the actual foreign key constraint name
        $constraintName = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'insurance_companies' 
            AND COLUMN_NAME = 'business_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (!empty($constraintName)) {
            $constraintName = $constraintName[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `insurance_companies` DROP FOREIGN KEY `{$constraintName}`");
        }
        
        // Now modify the column to be nullable
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->change();
        });
        
        // Re-add the foreign key constraint as nullable
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            // Drop the nullable foreign key constraint
            $table->dropForeign(['business_id']);
            // Make the column required again
            $table->foreignId('business_id')->nullable(false)->change();
            // Re-add the original foreign key constraint
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }
};
