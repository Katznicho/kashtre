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
        
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('reference')->index(); // used for lookups
            $table->string('external_reference')->nullable()->index();
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'processing'])->nullable()->index();
            $table->enum("type", ["credit", "debit"])->index();
            $table->enum("origin", ["api", "mobile", "web", 'payment_link'])->index();
            $table->string("phone_number")->nullable();
            $table->enum('provider', ['mtn', 'airtel', 'yo'])->index();
            $table->string('service');
            $table->date('date')->default(now())->index();
            $table->string("currency")->default('UGX');
            $table->string("names")->nullable();
            $table->string("email")->nullable();
            $table->string("ip_address")->nullable();
            $table->string("user_agent")->nullable();
            $table->string('method')->default('card'); // card, mobile_money, bank_transfer, crypto
            $table->enum('transaction_for', ['main', 'charge'])->default('main')->index();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints with explicit names
            $table->foreign('business_id', 'transactions_business_id_fk')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id', 'transactions_branch_id_fk')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
