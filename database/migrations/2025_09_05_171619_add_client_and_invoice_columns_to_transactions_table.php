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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('branch_id');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('client_id');
            
            // Add foreign key constraints
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['client_id', 'invoice_id']);
        });
    }
};
