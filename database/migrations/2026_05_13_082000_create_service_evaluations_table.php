<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comments')->nullable();
            $table->dateTime('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'service_id']);
            $table->index(['organization_id', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_evaluations');
    }
};
