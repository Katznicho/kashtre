<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_companies', 'open_enrollment_enabled')) {
                $table->boolean('open_enrollment_enabled')
                    ->default(false)
                    ->after('currency_code')
                    ->comment('Whether this vendor supports open enrollment mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_companies', 'open_enrollment_enabled')) {
                $table->dropColumn('open_enrollment_enabled');
            }
        });
    }
};
