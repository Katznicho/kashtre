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
        Schema::create('service_point_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_point_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure unique supervisor per service point (one supervisor per service point)
            $table->unique('service_point_id');
            
            // Index for better query performance
            $table->index(['business_id', 'supervisor_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_point_supervisors');
    }
};
