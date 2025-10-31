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
        Schema::create('contractor_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('contractor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('withdrawal_charge', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('withdrawal_type')->default('regular');
            $table->enum('status', ['pending', 'business_approved', 'kashtre_approved', 'approved', 'rejected', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->string('payment_method')->default('bank_transfer');
            $table->integer('business_approvals_count')->default(0);
            $table->integer('kashtre_approvals_count')->default(0);
            $table->integer('required_business_approvals')->default(3);
            $table->integer('required_kashtre_approvals')->default(3);
            $table->integer('current_business_step')->default(1);
            $table->integer('current_kashtre_step')->default(1);
            $table->integer('business_step_1_approvals')->default(0);
            $table->integer('business_step_2_approvals')->default(0);
            $table->integer('business_step_3_approvals')->default(0);
            $table->integer('kashtre_step_1_approvals')->default(0);
            $table->integer('kashtre_step_2_approvals')->default(0);
            $table->integer('kashtre_step_3_approvals')->default(0);
            $table->timestamp('business_approved_at')->nullable();
            $table->timestamp('kashtre_approved_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('transaction_reference')->nullable();
            $table->string('request_type')->default('contractor_withdrawal');
            $table->foreignId('related_request_id')->nullable()->constrained('contractor_withdrawal_requests')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_withdrawal_requests');
    }
};
