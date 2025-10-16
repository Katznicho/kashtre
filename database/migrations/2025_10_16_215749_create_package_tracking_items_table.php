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
        Schema::create('package_tracking_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('package_tracking_id')->constrained('package_tracking')->onDelete('cascade');
            $table->foreignId('included_item_id')->constrained('items')->onDelete('cascade');
            $table->integer('total_quantity'); // Total quantity available for this included item
            $table->integer('used_quantity')->default(0); // Quantity already used
            $table->integer('remaining_quantity'); // Remaining quantity
            $table->decimal('item_price', 15, 2); // Individual item price
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure no duplicate tracking items for the same package and included item
            $table->unique(['package_tracking_id', 'included_item_id'], 'unique_package_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_tracking_items');
    }
};
