<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->superAdmin = User::factory()->create(['status' => 'active']);
    $this->superAdmin->assignRole('super_admin');

    $this->associationManager = User::factory()->create(['status' => 'active']);
    $this->associationManager->assignRole('association_manager');
});

it('lets super_admin reach the admin Organizations index', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/organizations')
        ->assertOk();
});

it('lets super_admin reach the admin Users index', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/users')
        ->assertOk();
});

it('lets super_admin reach the admin Roles index', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/roles')
        ->assertOk();
});

it('lets super_admin reach the admin Activities index', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/activities')
        ->assertOk();
});

it('blocks association_manager from the admin panel resources', function () {
    $this->actingAs($this->associationManager)
        ->get('/admin/organizations')
        ->assertForbidden();
});
