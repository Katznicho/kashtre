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
            $table->boolean('admit_enable_credit')->default(false)->after('discharge_button_label')->comment('Enable credit services (/C suffix) when admitting clients');
            $table->boolean('admit_enable_long_stay')->default(false)->after('admit_enable_credit')->comment('Enable long-stay (/M suffix) when admitting clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['admit_enable_credit', 'admit_enable_long_stay']);
        });
    }
};
