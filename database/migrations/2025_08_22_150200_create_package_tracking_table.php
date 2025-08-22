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
        Schema::create('package_tracking', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_item_id')->constrained('items')->onDelete('cascade'); // The package item
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade'); // The item included in package
            $table->integer('total_quantity'); // Total quantity available in package
            $table->integer('used_quantity')->default(0); // Quantity already used
            $table->integer('remaining_quantity'); // Remaining quantity
            $table->date('valid_from');
            $table->date('valid_until');
            $table->enum('status', ['active', 'expired', 'fully_used', 'cancelled'])->default('active');
            $table->decimal('package_price', 15, 2); // Original package price
            $table->decimal('item_price', 15, 2); // Individual item price
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_tracking');
    }
};
