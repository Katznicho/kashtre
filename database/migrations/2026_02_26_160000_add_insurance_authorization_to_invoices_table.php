<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('insurance_authorization_reference', 64)->nullable()->after('notes');
            $table->decimal('insurance_client_total', 14, 2)->nullable()->after('insurance_authorization_reference');
            $table->decimal('insurance_insurance_total', 14, 2)->nullable()->after('insurance_client_total');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['insurance_authorization_reference', 'insurance_client_total', 'insurance_insurance_total']);
        });
    }
};
