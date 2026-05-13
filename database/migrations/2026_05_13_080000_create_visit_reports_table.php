<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visit_type')->default('office');
            $table->string('status')->default('planned');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('pre_visit_notes')->nullable();
            $table->text('summary')->nullable();
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('evidence_files')->nullable();
            $table->timestamps();

            $table->index(['consultant_user_id', 'status']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_reports');
    }
};
