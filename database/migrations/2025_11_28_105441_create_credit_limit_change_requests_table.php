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
        Schema::create('credit_limit_change_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('initiated_by')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('clcr_initiated_by_foreign');
            
            // Entity type: 'client' or 'third_party_payer'
            $table->string('entity_type');
            $table->foreignId('entity_id'); // client_id or third_party_payer_id
            
            $table->decimal('current_credit_limit', 15, 2)->default(0);
            $table->decimal('requested_credit_limit', 15, 2);
            $table->text('reason')->nullable();
            
            // Approval tracking
            $table->enum('status', ['pending', 'initiated', 'authorized', 'approved', 'rejected'])->default('pending');
            $table->integer('current_step')->default(1); // 1=initiator, 2=authorizer, 3=approver
            
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('initiated_at')->nullable();
            
            $table->foreignId('authorized_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('authorized_at')->nullable();
            
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['entity_type', 'entity_id']);
            $table->index('status');
            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_limit_change_requests');
    }
};
