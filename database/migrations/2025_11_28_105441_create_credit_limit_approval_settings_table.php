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
        Schema::create('credit_limit_approval_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id');
            $table->string('approver_type')->default('user'); // 'user' or 'contractor'
            $table->enum('approval_level', ['initiator', 'authorizer', 'approver']);
            $table->timestamps();
            
            // Ensure unique approver per business and approval level
            $table->unique(['business_id', 'approver_id', 'approver_type', 'approval_level'], 'unique_credit_approver_per_business_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_limit_approval_approvers');
    }
};
