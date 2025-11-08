<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_workflow_authorizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_workflow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['credit_note_workflow_id', 'user_id'], 'workflow_authorizer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_workflow_authorizers');
    }
};

