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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Add fields to track current approval step
            $table->integer('current_business_step')->default(1)->after('required_kashtre_approvals');
            $table->integer('current_kashtre_step')->default(1)->after('current_business_step');
            
            // Track approvals per step
            $table->integer('business_step_1_approvals')->default(0)->after('current_kashtre_step');
            $table->integer('business_step_2_approvals')->default(0)->after('business_step_1_approvals');
            $table->integer('business_step_3_approvals')->default(0)->after('business_step_2_approvals');
            $table->integer('kashtre_step_1_approvals')->default(0)->after('business_step_3_approvals');
            $table->integer('kashtre_step_2_approvals')->default(0)->after('kashtre_step_1_approvals');
            $table->integer('kashtre_step_3_approvals')->default(0)->after('kashtre_step_2_approvals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn([
                'current_business_step',
                'current_kashtre_step',
                'business_step_1_approvals',
                'business_step_2_approvals',
                'business_step_3_approvals',
                'kashtre_step_1_approvals',
                'kashtre_step_2_approvals',
                'kashtre_step_3_approvals',
            ]);
        });
    }
};