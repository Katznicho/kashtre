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
        Schema::create('balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User who made the change
            
            // Balance change details
            $table->decimal('previous_balance', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2); // Positive for credit, negative for debit
            $table->decimal('new_balance', 15, 2);
            
            // Transaction details
            $table->enum('transaction_type', ['credit', 'debit', 'adjustment', 'refund', 'payment']);
            $table->string('description');
            $table->string('reference_number')->nullable(); // Invoice number, receipt number, etc.
            $table->text('notes')->nullable();
            
            // Metadata
            $table->string('payment_method')->nullable(); // cash, mobile_money, card, etc.
            $table->string('payment_reference')->nullable(); // Transaction ID from payment provider
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['client_id', 'created_at']);
            $table->index(['business_id', 'created_at']);
            $table->index(['invoice_id']);
            $table->index(['transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_histories');
    }
};
