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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Client details
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('payment_phone')->nullable();
            $table->string('visit_id');
            
            // Invoice details
            $table->json('items'); // Array of items with quantity, price, etc.
            $table->decimal('subtotal', 15, 2);
            $table->decimal('package_adjustment', 15, 2)->default(0);
            $table->decimal('account_balance_adjustment', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2);
            
            // Payment information
            $table->json('payment_methods')->nullable();
            $table->string('payment_status')->default('pending'); // pending, paid, partial, cancelled
            $table->text('notes')->nullable();
            
            // Invoice status
            $table->string('status')->default('draft'); // draft, confirmed, printed, cancelled
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
