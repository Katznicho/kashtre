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
        Schema::table('clients', function (Blueprint $table) {
            // Add new fields for client
            $table->string('tin_number')->nullable()->after('nin');
            $table->string('village')->nullable()->after('address');
            $table->string('county')->nullable()->after('village');
            
            // Add new fields for next of kin
            $table->string('nok_sex')->nullable()->after('nok_other_names');
            $table->string('nok_village')->nullable()->after('nok_physical_address');
            $table->string('nok_county')->nullable()->after('nok_village');
            
            // Drop the id_passport_no field as requested
            $table->dropColumn('id_passport_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['tin_number', 'village', 'county', 'nok_sex', 'nok_village', 'nok_county']);
            
            // Restore the id_passport_no field
            $table->string('id_passport_no')->nullable()->after('nin');
        });
    }
};
