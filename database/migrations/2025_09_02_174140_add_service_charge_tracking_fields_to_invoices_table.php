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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_service_charge_processed')->default(false)->after('printed_at');
            $table->timestamp('service_charge_processed_at')->nullable()->after('is_service_charge_processed');
            $table->unsignedBigInteger('service_charge_processed_by_user_id')->nullable()->after('service_charge_processed_at');
            
            $table->foreign('service_charge_processed_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['service_charge_processed_by_user_id']);
            $table->dropColumn(['is_service_charge_processed', 'service_charge_processed_at', 'service_charge_processed_by_user_id']);
        });
    }
};
