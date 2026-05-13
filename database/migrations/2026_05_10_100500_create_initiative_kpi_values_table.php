<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_kpi_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained()->cascadeOnDelete();

            // Submitted by association
            $table->string('baseline')->nullable();
            $table->string('target')->nullable();

            // Filled by Excellence reviewer
            $table->unsignedTinyInteger('score')->nullable(); // 0..5
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            $table->unique(['initiative_id', 'kpi_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_kpi_values');
    }
};
