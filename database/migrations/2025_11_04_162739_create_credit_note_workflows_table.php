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
        Schema::create('credit_note_workflows', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Technical Supervisor - Verifies');
            $table->foreignId('finance_user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Finance - Authorises Refund');
            $table->foreignId('ceo_user_id')->nullable()->constrained('users')->onDelete('set null')->comment('CEO - Approves Refund');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure one workflow per business
            $table->unique('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_workflows');
    }
};
