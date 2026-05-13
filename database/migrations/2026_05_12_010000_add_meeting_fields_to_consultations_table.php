<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->string('meeting_provider', 40)->nullable()->after('scheduled_at');
            $table->string('meeting_id')->nullable()->after('meeting_provider');
            $table->string('meeting_url')->nullable()->after('meeting_id');
            $table->string('meeting_password')->nullable()->after('meeting_url');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table): void {
            $table->dropColumn([
                'meeting_provider',
                'meeting_id',
                'meeting_url',
                'meeting_password',
            ]);
        });
    }
};
