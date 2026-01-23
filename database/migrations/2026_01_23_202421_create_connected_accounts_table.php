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
        Schema::create('connected_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // Kashtre client ID
            $table->unsignedBigInteger('third_party_business_id'); // Third-party insurance company/business ID
            $table->unsignedBigInteger('third_party_user_id')->nullable(); // Third-party user ID (if user exists)
            $table->string('third_party_username')->nullable(); // Username in third-party system
            $table->string('connection_type')->default('client'); // client, payer, etc.
            $table->string('status')->default('active'); // active, inactive, pending
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            
            // Indexes
            $table->index(['client_id', 'third_party_business_id']);
            $table->index('third_party_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connected_accounts');
    }
};
