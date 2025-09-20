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
        Schema::create('package_sales', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Client name
            $table->string('invoice_number'); // Invoice number where package item was sold
            $table->string('pkn'); // Package tracking number (PKN)
            $table->date('date'); // Date of sale
            $table->integer('qty'); // Quantity sold
            $table->string('item_name'); // Item name that was sold
            $table->decimal('amount', 10, 2); // Amount for this sale
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('package_tracking_id'); // Reference to package tracking record
            $table->unsignedBigInteger('item_id'); // Reference to the item that was sold
            $table->string('status')->default('completed'); // Sale status
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('package_tracking_id')->references('id')->on('package_tracking')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['business_id', 'branch_id']);
            $table->index(['client_id', 'date']);
            $table->index(['package_tracking_id']);
            $table->index(['invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_sales');
    }
};
