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
        Schema::table('accounts_receivable', function (Blueprint $table) {
            $table->foreignId('third_party_payer_id')->nullable()->after('client_id')->constrained('third_party_payers')->onDelete('cascade');
            $table->index(['third_party_payer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts_receivable', function (Blueprint $table) {
            $table->dropForeign(['third_party_payer_id']);
            $table->dropIndex(['third_party_payer_id', 'status']);
            $table->dropColumn('third_party_payer_id');
        });
    }
};
