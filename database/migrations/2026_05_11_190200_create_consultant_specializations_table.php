<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_specializations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('consultant_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('specialization', 120);
            $table->timestamps();

            $table->unique(
                ['consultant_user_id', 'specialization'],
                'consultant_specs_user_specialization_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_specializations');
    }
};
