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
        Schema::create('credit_limit_change_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_limit_change_request_id')
                ->constrained('credit_limit_change_requests')
                ->onDelete('cascade')
                ->name('clcr_approvals_request_id_foreign');
            $table->foreignId('approver_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('clcr_approvals_approver_id_foreign');
            $table->enum('approval_level', ['initiator', 'authorizer', 'approver']);
            $table->enum('action', ['approved', 'rejected'])->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Ensure unique approval per request, approver, and level
            $table->unique(['credit_limit_change_request_id', 'approver_id', 'approval_level'], 'unique_credit_approval_per_request_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_limit_change_request_approvals');
    }
};
