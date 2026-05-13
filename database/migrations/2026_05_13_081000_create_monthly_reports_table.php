<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('report_month');
            $table->string('status')->default('draft');
            $table->text('executive_summary')->nullable();
            $table->text('progress_summary')->nullable();
            $table->text('risks_summary')->nullable();
            $table->text('questions_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('attachments')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['initiative_id', 'report_month']);
            $table->index(['consultant_user_id', 'status']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
