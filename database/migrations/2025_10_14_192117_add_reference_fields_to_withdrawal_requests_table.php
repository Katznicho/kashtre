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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Add fields to link related withdrawal requests
            if (!Schema::hasColumn('withdrawal_requests', 'transaction_reference')) {
                $table->string('transaction_reference')->nullable()->after('uuid'); // Common reference for both charge and amount requests
            }
            if (!Schema::hasColumn('withdrawal_requests', 'request_type')) {
                $table->enum('request_type', ['charge', 'amount'])->nullable()->after('transaction_reference'); // Type of request (charge or amount)
            }
            if (!Schema::hasColumn('withdrawal_requests', 'related_request_id')) {
                $table->foreignId('related_request_id')->nullable()->constrained('withdrawal_requests')->onDelete('set null')->after('request_type'); // Link to related request
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['related_request_id']);
            $table->dropColumn(['transaction_reference', 'request_type', 'related_request_id']);
        });
    }
};