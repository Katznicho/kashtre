<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        // Schema::table('departments', function (Blueprint $table) {
        //     $table->dropUnique(['name']);
        // });

        Schema::table('qualifications', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });


    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->unique('name');
        });
        
    }
};
