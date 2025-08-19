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
        Schema::table('service_charges', function (Blueprint $table) {
            // Add new fields
            $table->decimal('upper_bound', 10, 2)->nullable()->after('amount');
            $table->decimal('lower_bound', 10, 2)->nullable()->after('upper_bound');
            
            // Remove the name field
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_charges', function (Blueprint $table) {
            // Add back the name field
            $table->string('name')->after('entity_id');
            
            // Remove the new fields
            $table->dropColumn(['upper_bound', 'lower_bound']);
        });
    }
};
