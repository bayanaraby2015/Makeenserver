<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Personal contact
            $table->string('phone')->nullable()->after('email');

            // Locale preference (see config/brand.php — platform supports ar/en)
            $table->string('locale', 5)->default('ar')->after('phone');

            // Lifecycle: pending = awaits admin approval (self-registered associations)
            //           active = can log in
            //           suspended = blocked but record retained
            $table->enum('status', ['pending', 'active', 'suspended'])
                ->default('pending')
                ->index()
                ->after('locale');

            // Primary organization (e.g. the association the user belongs to).
            // Consultants serving multiple orgs use a separate pivot in Sprint 4.
            // super_admin and excellence_team members may have NULL here.
            $table->foreignId('primary_organization_id')
                ->nullable()
                ->after('status')
                ->constrained('organizations')
                ->nullOnDelete();

            // Login telemetry
            $table->timestamp('last_login_at')->nullable()->after('primary_organization_id');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_organization_id']);
            $table->dropColumn([
                'phone',
                'locale',
                'status',
                'primary_organization_id',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
