<?php

use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->org = Organization::factory()->create([
        'type' => 'association',
        'status' => 'active',
    ]);

    $this->manager = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->org->id,
    ]);
    $this->manager->assignRole('association_manager');

    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->assignRole('super_admin');

    $this->otherOrg = Organization::factory()->create(['type' => 'association']);
    $this->stranger = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->otherOrg->id,
    ]);
    $this->stranger->assignRole('association_manager');
});

it('lets the owning manager update and delete a draft initiative', function () {
    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->create(['status' => 'draft']);

    expect($this->manager->can('update', $initiative))->toBeTrue()
        ->and($this->manager->can('delete', $initiative))->toBeTrue()
        ->and($this->manager->can('submit', $initiative))->toBeTrue();
});

it('blocks the owning manager from editing/deleting a submitted initiative', function () {
    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->submitted()
        ->create();

    expect($this->manager->can('update', $initiative))->toBeFalse()
        ->and($this->manager->can('delete', $initiative))->toBeFalse()
        ->and($this->manager->can('submit', $initiative))->toBeFalse();
});

it('lets the owning manager edit when revisions are requested', function () {
    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->create(['status' => 'revisions_requested']);

    expect($this->manager->can('update', $initiative))->toBeTrue()
        ->and($this->manager->can('submit', $initiative))->toBeTrue()
        // Delete is only allowed for draft (not for revisions_requested).
        ->and($this->manager->can('delete', $initiative))->toBeFalse();
});

it('prevents managers from editing initiatives owned by other organizations', function () {
    $initiative = Initiative::factory()
        ->for($this->otherOrg, 'organization')
        ->create(['status' => 'draft']);

    expect($this->manager->can('view', $initiative))->toBeFalse()
        ->and($this->manager->can('update', $initiative))->toBeFalse()
        ->and($this->manager->can('delete', $initiative))->toBeFalse();
});

it('allows super_admin to manage initiatives regardless of status', function () {
    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->submitted()
        ->create();

    expect($this->admin->can('update', $initiative))->toBeTrue()
        ->and($this->admin->can('delete', $initiative))->toBeTrue();
});
