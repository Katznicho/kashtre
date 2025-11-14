<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('payment_method_account_id');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            
            // Foreign key with custom shorter name
            $table->foreign('payment_method_account_id', 'pm_account_trans_pm_account_id_foreign')
                ->references('id')
                ->on('payment_method_accounts')
                ->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['debit', 'credit'])->index();
            $table->string('reference')->index(); // Transaction reference
            $table->string('external_reference')->nullable()->index(); // External payment reference
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed')->index();
            $table->decimal('balance_before', 15, 2)->nullable(); // Account balance before transaction
            $table->decimal('balance_after', 15, 2)->nullable(); // Account balance after transaction
            $table->string('currency')->default('UGX');
            $table->string('transaction_for')->default('payment_received'); // payment_received, manual_adjustment, etc.
            $table->json('metadata')->nullable(); // Additional data
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_account_transactions');
    }
};
