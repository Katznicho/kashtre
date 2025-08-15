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
            // Remove the address fields that are no longer needed
            $table->dropColumn(['address', 'nok_physical_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore the address fields if needed
            $table->string('address')->nullable()->after('phone_number');
            $table->string('nok_physical_address')->nullable()->after('nok_phone_number');
        });
    }
};
