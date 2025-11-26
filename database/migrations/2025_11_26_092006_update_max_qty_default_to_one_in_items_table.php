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
        // First, update existing NULL values to 1
        DB::table('items')->whereNull('max_qty')->update(['max_qty' => 1]);
        
        // Then alter the column to set default to 1 and make it NOT NULL
        Schema::table('items', function (Blueprint $table) {
            $table->integer('max_qty')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('max_qty')->nullable()->change();
        });
    }
};
