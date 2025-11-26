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
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Financial details
            $table->decimal('amount_due', 15, 2)->comment('Total amount due for this invoice');
            $table->decimal('amount_paid', 15, 2)->default(0)->comment('Amount paid so far');
            $table->decimal('balance', 15, 2)->comment('Outstanding balance (amount_due - amount_paid)');
            
            // Dates
            $table->date('invoice_date')->comment('Date when invoice was created');
            $table->date('due_date')->nullable()->comment('Expected payment due date');
            
            // Aging information
            $table->integer('days_past_due')->default(0)->comment('Number of days past due date');
            $table->enum('aging_bucket', ['current', 'days_30_60', 'days_60_90', 'over_90'])->default('current')->comment('Aging bucket category');
            
            // Status
            $table->enum('status', ['current', 'overdue', 'paid', 'partial'])->default('current')->comment('Current status of the receivable');
            $table->enum('payer_type', ['first_party', 'third_party'])->default('first_party')->comment('Type of payer: first party (client) or third party');
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional metadata');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['client_id', 'status']);
            $table->index(['business_id', 'status']);
            $table->index(['aging_bucket', 'status']);
            $table->index('due_date');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_receivable');
    }
};
