<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 1 — extend Spatie's activity_log table with platform-specific
 * audit columns required by ADR-0003 (Layered Activity Log Strategy).
 *
 * Existing Spatie columns:
 *   id, log_name, description, subject_type, subject_id,
 *   causer_type, causer_id, properties JSON, batch_uuid, event,
 *   created_at, updated_at
 *
 * Added columns (from ADR-0003):
 *   ip_address      — request origin
 *   user_agent      — browser/client identification
 *   event_category  — high-level grouping for filtering UI
 *   severity        — used by alerting UI (badges, notifications)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('properties');
            $table->text('user_agent')->nullable()->after('ip_address');

            $table->enum('event_category', ['data', 'security', 'workflow', 'system', 'notification'])
                ->default('data')
                ->index()
                ->after('user_agent');

            $table->enum('severity', ['info', 'warning', 'critical'])
                ->default('info')
                ->index()
                ->after('event_category');
        });
    }

    public function down(): void
    {
        Schema::table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'event_category', 'severity']);
        });
    }
};
