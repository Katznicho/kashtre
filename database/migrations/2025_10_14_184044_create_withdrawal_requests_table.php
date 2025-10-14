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
        if (!Schema::hasTable('withdrawal_requests')) {
            Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('withdrawal_charge', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->enum('withdrawal_type', ['regular', 'express']);
            $table->enum('status', [
                'pending', 
                'business_approved', 
                'kashtre_approved', 
                'approved', 
                'rejected', 
                'processing', 
                'completed', 
                'failed'
            ])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Payment details
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'mobile_money']);
            
            // Approval tracking
            $table->integer('business_approvals_count')->default(0);
            $table->integer('kashtre_approvals_count')->default(0);
            $table->integer('required_business_approvals')->default(3);
            $table->integer('required_kashtre_approvals')->default(3);
            
            // Timestamps for different stages
            $table->timestamp('business_approved_at')->nullable();
            $table->timestamp('kashtre_approved_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Processor information
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
