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
        Schema::create('payment_method_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Account name (e.g., "Mobile Money Account - Yo Uganda")
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', [
                'insurance',
                'credit_arrangement',
                'mobile_money',
                'v_card',
                'p_card',
                'bank_transfer',
                'cash'
            ]);
            $table->string('account_number')->nullable(); // External account number if applicable
            $table->string('account_holder_name')->nullable(); // Account holder name
            $table->string('provider')->nullable(); // Provider (e.g., "Yo Uganda", "MTN Mobile Money", "Bank Name")
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency')->default('UGX');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint: one account per payment method per business/provider combination
            $table->unique(['business_id', 'payment_method', 'provider'], 'unique_payment_method_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_accounts');
    }
};
