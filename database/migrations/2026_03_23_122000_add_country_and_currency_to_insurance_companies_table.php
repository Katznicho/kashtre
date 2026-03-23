<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_companies', 'country_id')) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('phone')
                    ->constrained('countries')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('insurance_companies', 'currency_code')) {
                $table->string('currency_code', 10)
                    ->default('UGX')
                    ->after('country_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_companies', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }

            if (Schema::hasColumn('insurance_companies', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
        });
    }
};

