<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

dataset('panel_role_matrix', [
    // [panel_id, role, expected_can_access]
    ['admin', 'super_admin', true],
    ['admin', 'association_manager', false],
    ['excellence', 'excellence_manager', true],
    ['excellence', 'excellence_member', true],
    ['excellence', 'donor_admin', false],
    ['donor', 'donor_admin', true],
    ['donor', 'consultant', false],
    ['consultant', 'consultant', true],
    ['consultant', 'excellence_member', false],
    ['association', 'association_manager', true],
    ['association', 'association_member', true],
    ['association', 'donor_admin', false],
]);

it('grants or denies panel access according to role mapping', function (
    string $panelId,
    string $role,
    bool $expected,
) {
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role);

    $panel = Filament::getPanel($panelId);

    expect($user->canAccessPanel($panel))->toBe($expected);
})->with('panel_role_matrix');

it('blocks pending users from every panel even with the right role', function () {
    $user = User::factory()->create(['status' => 'pending']);
    $user->assignRole('association_manager');

    $panel = Filament::getPanel('association');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('blocks suspended users from every panel even with super_admin role', function () {
    $user = User::factory()->create(['status' => 'suspended']);
    $user->assignRole('super_admin');

    foreach (['admin', 'excellence', 'donor', 'consultant', 'association'] as $panelId) {
        expect($user->canAccessPanel(Filament::getPanel($panelId)))->toBeFalse();
    }
});

it('lets super_admin into every panel when active', function () {
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('super_admin');

    foreach (['admin', 'excellence', 'donor', 'consultant', 'association'] as $panelId) {
        expect($user->canAccessPanel(Filament::getPanel($panelId)))->toBeTrue();
    }
});
