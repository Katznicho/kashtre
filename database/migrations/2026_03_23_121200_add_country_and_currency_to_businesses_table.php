<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('address')->constrained('countries')->nullOnDelete();
            }

            if (!Schema::hasColumn('businesses', 'currency_code')) {
                $table->string('currency_code', 10)->default('UGX')->after('country_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }

            if (Schema::hasColumn('businesses', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
        });
    }
};

