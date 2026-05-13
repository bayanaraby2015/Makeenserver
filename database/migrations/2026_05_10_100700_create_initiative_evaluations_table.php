<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('recommendation')->nullable();

            $table->string('decision', 32)->default('pending');
            // pending | approved | revisions_requested | rejected

            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['initiative_id', 'evaluator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_evaluations');
    }
};
