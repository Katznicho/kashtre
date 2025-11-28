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
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('discharge_remove_credit')->default(false)->after('admit_enable_long_stay')->comment('Remove credit services (/C suffix) when discharging clients');
            $table->boolean('discharge_remove_long_stay')->default(true)->after('discharge_remove_credit')->comment('Remove long-stay (/M suffix) when discharging clients - always true');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['discharge_remove_credit', 'discharge_remove_long_stay']);
        });
    }
};
