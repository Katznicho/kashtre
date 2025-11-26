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
            $table->string('admit_button_label')->nullable()->after('max_first_party_credit_limit')->default('Admit Patient')->comment('Configurable label for the admit button');
            $table->string('discharge_button_label')->nullable()->after('admit_button_label')->default('Discharge Patient')->comment('Configurable label for the discharge button');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['admit_button_label', 'discharge_button_label']);
        });
    }
};
