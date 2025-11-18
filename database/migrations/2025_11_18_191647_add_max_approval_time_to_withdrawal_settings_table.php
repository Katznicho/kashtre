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
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->integer('max_approval_time')->nullable()->after('number_of_free_withdrawals_per_day')->comment('Maximum approval time in hours or days');
            $table->enum('max_approval_time_unit', ['hours', 'days'])->nullable()->after('max_approval_time')->default('hours')->comment('Unit for maximum approval time (hours or days)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropColumn(['max_approval_time', 'max_approval_time_unit']);
        });
    }
};
