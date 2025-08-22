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
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_account_id')->constrained('money_accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('money_accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('UGX');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('transfer_type', [
                'payment_received',
                'order_confirmed',
                'service_delivered',
                'refund_approved',
                'package_usage',
                'service_charge',
                'manual_transfer'
            ]);
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('package_usage_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reference')->nullable(); // External reference (TID, order number, etc.)
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_transfers');
    }
};
