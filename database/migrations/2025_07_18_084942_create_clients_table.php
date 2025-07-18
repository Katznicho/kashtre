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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['Out Patient', 'In Patient'])->default('Out Patient');
            $table->string('client_id')->unique(); // System-generated permanent ID (linked to NIN)
            $table->string('visit_id'); // System-generated permanent ID (linked to NIN)
            $table->string('nin')->nullable();     // National Identification Number
            $table->string('name');
            $table->integer('age')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->string('phone_number');
            $table->date('date_of_birth')->nullable();
            $table->string("payment_phone_number")->nullable();
            $table->string('address');
            $table->string('balance')->default(0);
            $table->string('email');
            $table->string('next_of_kin')->nullable();
            $table->enum('preferred_payment_method', ['cash', 'bank_transfer', 'credit_card', 'insurance', 'postpaid', 'mobile_money'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
