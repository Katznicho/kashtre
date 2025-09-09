<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'save_and_exit' to the transfer_type enum
        DB::statement("ALTER TABLE money_transfers MODIFY COLUMN transfer_type ENUM(
            'payment_received',
            'order_confirmed',
            'service_delivered',
            'refund_approved',
            'package_usage',
            'service_charge',
            'manual_transfer',
            'save_and_exit'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'save_and_exit' from the transfer_type enum
        DB::statement("ALTER TABLE money_transfers MODIFY COLUMN transfer_type ENUM(
            'payment_received',
            'order_confirmed',
            'service_delivered',
            'refund_approved',
            'package_usage',
            'service_charge',
            'manual_transfer'
        )");
    }
};
