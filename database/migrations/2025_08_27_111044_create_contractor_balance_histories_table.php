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
        Schema::create('contractor_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('money_account_id')->constrained()->onDelete('cascade');
            $table->decimal('previous_balance', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->decimal('new_balance', 15, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('description');
            $table->string('reference_type')->nullable(); // invoice, service_delivery, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the related record
            $table->json('metadata')->nullable(); // Additional data
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who performed the action
            $table->timestamps();
            
            $table->index(['contractor_profile_id', 'created_at'], 'contractor_balance_histories_profile_created_idx');
            $table->index(['money_account_id', 'created_at'], 'contractor_balance_histories_account_created_idx');
            $table->index(['reference_type', 'reference_id'], 'contractor_balance_histories_reference_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_balance_histories');
    }
};
