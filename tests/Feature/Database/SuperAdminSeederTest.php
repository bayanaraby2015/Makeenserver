<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Hash;

/**
 * Set an env value through every channel Laravel's env() helper consults
 * (Env repository, $_ENV, $_SERVER, putenv) so the override wins even when
 * a real .env file is present (as in CI).
 */
function setSuperAdminEnv(string $key, string $value): void
{
    Env::getRepository()->set($key, $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates an active super_admin user from env credentials', function () {
    config()->set('makeen.roles.super_admin', 'super_admin');
    setSuperAdminEnv('SUPER_ADMIN_EMAIL', 'devops@example.test');
    setSuperAdminEnv('SUPER_ADMIN_NAME', 'DevOps');
    setSuperAdminEnv('SUPER_ADMIN_PASSWORD', 'ChosenPass123');

    $this->seed(SuperAdminSeeder::class);

    $user = User::firstWhere('email', 'devops@example.test');

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('DevOps')
        ->and($user->status)->toBe('active')
        ->and($user->locale)->toBe('ar')
        ->and(Hash::check('ChosenPass123', $user->password))->toBeTrue()
        ->and($user->hasRole('super_admin'))->toBeTrue();
});

it('is idempotent — running twice does not duplicate or downgrade the user', function () {
    setSuperAdminEnv('SUPER_ADMIN_EMAIL', 'ops@example.test');
    setSuperAdminEnv('SUPER_ADMIN_NAME', 'Ops');
    setSuperAdminEnv('SUPER_ADMIN_PASSWORD', 'FirstPass123');

    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $count = User::where('email', 'ops@example.test')->count();
    expect($count)->toBe(1);

    $user = User::firstWhere('email', 'ops@example.test');
    expect($user->hasRole('super_admin'))->toBeTrue();
});
