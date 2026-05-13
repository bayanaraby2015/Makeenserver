<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_reports', function (Blueprint $table): void {
            $table->json('appointment_options')->nullable()->after('scheduled_at');
            $table->dateTime('selected_at')->nullable()->after('appointment_options');
            $table->foreignId('selected_by')->nullable()->after('selected_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visit_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('selected_by');
            $table->dropColumn(['appointment_options', 'selected_at']);
        });
    }
};
