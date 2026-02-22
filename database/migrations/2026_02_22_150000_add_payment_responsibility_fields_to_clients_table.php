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
            // Payment responsibility fields from insurance policy
            $table->boolean('has_deductible')->nullable()->after('policy_number');
            $table->decimal('deductible_amount', 15, 2)->nullable()->after('has_deductible');
            $table->decimal('copay_amount', 15, 2)->nullable()->after('deductible_amount');
            $table->decimal('coinsurance_percentage', 5, 2)->nullable()->after('copay_amount');
            $table->decimal('copay_max_limit', 15, 2)->nullable()->after('coinsurance_percentage');
            $table->boolean('copay_contributes_to_deductible')->nullable()->after('copay_max_limit');
            $table->boolean('coinsurance_contributes_to_deductible')->nullable()->after('copay_contributes_to_deductible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'has_deductible',
                'deductible_amount',
                'copay_amount',
                'coinsurance_percentage',
                'copay_max_limit',
                'copay_contributes_to_deductible',
                'coinsurance_contributes_to_deductible',
            ]);
        });
    }
};
