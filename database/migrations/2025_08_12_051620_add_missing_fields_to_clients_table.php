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
            // Add missing fields that don't exist yet
            $table->string('surname')->nullable()->after('nin');
            $table->string('first_name')->nullable()->after('surname');
            $table->string('other_names')->nullable()->after('first_name');
            $table->string('id_passport_no')->nullable()->after('other_names');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('sex');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->enum('services_category', ['dental', 'optical', 'outpatient', 'inpatient', 'maternity', 'funeral'])->nullable()->after('address');
            
            // Add Next of Kin details columns
            $table->string('nok_surname')->nullable()->after('preferred_payment_method');
            $table->string('nok_first_name')->nullable()->after('nok_surname');
            $table->string('nok_other_names')->nullable()->after('nok_first_name');
            $table->enum('nok_marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('nok_other_names');
            $table->string('nok_occupation')->nullable()->after('nok_marital_status');
            $table->string('nok_phone_number')->nullable()->after('nok_occupation');
            $table->text('nok_physical_address')->nullable()->after('nok_phone_number');
            
            // Add status field
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn([
                'surname', 'first_name', 'other_names', 'id_passport_no', 
                'marital_status', 'occupation', 'services_category',
                'nok_surname', 'nok_first_name', 'nok_other_names', 'nok_marital_status',
                'nok_occupation', 'nok_phone_number', 'nok_physical_address', 'status'
            ]);
        });
    }
};
