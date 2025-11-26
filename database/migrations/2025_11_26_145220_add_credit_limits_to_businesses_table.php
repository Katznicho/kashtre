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
            $table->decimal('max_third_party_credit_limit', 15, 2)->nullable()->after('visit_id_format')->comment('Maximum amount of credit the entity can accept from a third party payer');
            $table->decimal('max_first_party_credit_limit', 15, 2)->nullable()->after('max_third_party_credit_limit')->comment('Maximum amount of credit the entity can accept from a first party payer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['max_third_party_credit_limit', 'max_first_party_credit_limit']);
        });
    }
};
