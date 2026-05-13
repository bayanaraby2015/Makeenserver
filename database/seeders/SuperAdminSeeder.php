<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the default super_admin account on a fresh install.
 *
 * Credentials come from .env (so they can be rotated without code changes):
 *
 *   SUPER_ADMIN_NAME      (default: مدير النظام)
 *   SUPER_ADMIN_EMAIL     (default: admin@makeen.local)
 *   SUPER_ADMIN_PASSWORD  (REQUIRED in production — no default)
 *
 * If SUPER_ADMIN_PASSWORD is missing, this seeder generates a random one
 * and prints it once to stdout so the operator can capture it.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('SUPER_ADMIN_EMAIL', 'admin@makeen.local');
        $name = (string) env('SUPER_ADMIN_NAME', 'مدير النظام');
        $password = env('SUPER_ADMIN_PASSWORD');
        $generated = false;

        if (empty($password)) {
            $password = bin2hex(random_bytes(8));
            $generated = true;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'status' => 'active',
                'locale' => 'ar',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([config('makeen.roles.super_admin')]);

        if ($generated) {
            $this->command?->warn('SuperAdminSeeder generated a random password.');
            $this->command?->warn("Email:    {$email}");
            $this->command?->warn("Password: {$password}");
            $this->command?->warn('Save this password — it will NOT be shown again.');
        } else {
            $this->command?->info("super_admin ensured for {$email} (password from env).");
        }
    }
}
