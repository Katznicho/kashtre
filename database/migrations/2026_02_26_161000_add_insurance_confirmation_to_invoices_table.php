<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('insurance_confirmation_code', 64)->nullable()->after('insurance_insurance_total');
            $table->timestamp('insurance_authorized_at')->nullable()->after('insurance_confirmation_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['insurance_confirmation_code', 'insurance_authorized_at']);
        });
    }
};
