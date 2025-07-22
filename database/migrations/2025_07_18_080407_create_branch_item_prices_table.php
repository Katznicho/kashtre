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
        Schema::create('branch_item_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('price')->default(0);
            $table->timestamps();
            $table->softDeletes(); // Allows for soft deletion of branch item prices
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_item_prices');
    }
};
