<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->index('status', 'consultations_status_index');
            $table->index(['consultant_user_id', 'status'], 'consultations_consultant_status_index');
        });

        Schema::table('visit_reports', function (Blueprint $table): void {
            $table->index('status', 'visit_reports_status_index');
            $table->index(['consultant_user_id', 'status'], 'visit_reports_consultant_status_index');
        });

        Schema::table('monthly_reports', function (Blueprint $table): void {
            $table->index('status', 'monthly_reports_status_index');
            $table->index(['consultant_user_id', 'status'], 'monthly_reports_consultant_status_index');
        });

        Schema::table('service_evaluations', function (Blueprint $table): void {
            $table->index('service_type', 'service_evaluations_service_type_index');
            $table->index('evaluator_id', 'service_evaluations_evaluator_index');
        });

        // The meeting_password column is encrypted at the application layer.
        // Encrypted ciphertext can exceed the original VARCHAR(255) length
        // (~250 chars for a short password), so widen it to TEXT.
        Schema::table('consultations', function (Blueprint $table): void {
            $table->text('meeting_password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->dropIndex('consultations_status_index');
            $table->dropIndex('consultations_consultant_status_index');
            $table->string('meeting_password')->nullable()->change();
        });

        Schema::table('visit_reports', function (Blueprint $table): void {
            $table->dropIndex('visit_reports_status_index');
            $table->dropIndex('visit_reports_consultant_status_index');
        });

        Schema::table('monthly_reports', function (Blueprint $table): void {
            $table->dropIndex('monthly_reports_status_index');
            $table->dropIndex('monthly_reports_consultant_status_index');
        });

        Schema::table('service_evaluations', function (Blueprint $table): void {
            $table->dropIndex('service_evaluations_service_type_index');
            $table->dropIndex('service_evaluations_evaluator_index');
        });
    }
};
