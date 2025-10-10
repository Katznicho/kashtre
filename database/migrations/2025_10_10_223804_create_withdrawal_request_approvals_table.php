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
        Schema::create('withdrawal_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_request_id')->constrained('withdrawal_requests')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->enum('approver_level', ['business', 'kashtre']);
            $table->enum('action', ['approved', 'rejected']);
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate approvals
            $table->unique(['withdrawal_request_id', 'approver_id'], 'wr_approval_unique');
            
            // Indexes for better query performance
            $table->index(['withdrawal_request_id', 'approver_level'], 'wr_approval_level_idx');
            $table->index('action', 'wr_action_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_request_approvals');
    }
};
