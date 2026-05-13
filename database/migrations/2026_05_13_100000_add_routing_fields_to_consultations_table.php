<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->string('request_type', 60)->default('consultation')->after('attachments');
            $table->string('routing_target', 60)->nullable()->after('request_type');
            $table->foreignId('responsible_user_id')->nullable()->after('consultant_user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('responsible_user_id');
            $table->dropColumn(['request_type', 'routing_target']);
        });
    }
};
