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
        Schema::table('clients', function (Blueprint $table) {
            // Drop the old client_type enum
            $table->dropColumn('client_type');
            
            // Add boolean flags for credit eligibility and long-stay status
            $table->boolean('is_credit_eligible')->default(false)->after('status')->comment('If true, adds /C suffix to visit ID for credit services');
            $table->boolean('is_long_stay')->default(false)->after('is_credit_eligible')->comment('If true, adds /M suffix to visit ID for long-stay/inpatient. Visit ID won\'t expire until manually discharged.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore client_type enum
            $table->enum('client_type', ['regular', 'credit_eligible', 'long_stay'])->default('regular')->after('status');
            
            // Drop the boolean flags
            $table->dropColumn(['is_credit_eligible', 'is_long_stay']);
        });
    }
};
