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
        Schema::create('third_party_payer_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_payer_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null'); // Client who received the service
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Balance change details
            $table->decimal('previous_balance', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2); // Positive for credit, negative for debit
            $table->decimal('new_balance', 15, 2);
            
            // Transaction details
            $table->enum('transaction_type', ['credit', 'debit', 'adjustment', 'refund', 'payment']);
            $table->string('description');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            
            // Metadata
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_status')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (using shorter names to avoid MySQL identifier length limits)
            $table->index(['third_party_payer_id', 'created_at'], 'tpp_balance_hist_payer_created_idx');
            $table->index(['business_id', 'created_at'], 'tpp_balance_hist_business_created_idx');
            $table->index(['invoice_id'], 'tpp_balance_hist_invoice_idx');
            $table->index(['transaction_type'], 'tpp_balance_hist_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_payer_balance_histories');
    }
};
