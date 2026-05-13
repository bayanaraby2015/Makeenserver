<?php

use App\Filament\Association\Resources\Initiatives\Pages\EditInitiative;
use App\Filament\Association\Resources\Initiatives\Pages\ListInitiatives;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\InitiativeReviewedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

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

    $this->actingAs($this->manager);
});

it('marks a draft initiative as submitted and notifies admins on submit action', function () {
    Notification::fake();

    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->create(['status' => 'draft']);

    Livewire::test(EditInitiative::class, ['record' => $initiative->getRouteKey()])
        ->callAction('submit');

    $initiative->refresh();
    expect($initiative->status)->toBe('submitted')
        ->and($initiative->submitted_at)->not->toBeNull();

    Notification::assertSentTo(
        $this->admin,
        InitiativeReviewedNotification::class,
        fn ($n) => $n->event === 'submitted',
    );
});

it('blocks editing a submitted initiative via policy', function () {
    $initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->submitted()
        ->create();

    // The InitiativePolicy::update() returns false when status is submitted,
    // so even direct navigation to /association/initiatives/{id}/edit
    // must be denied — closing the security bypass reported by the user.
    expect($this->manager->can('update', $initiative))->toBeFalse()
        ->and($this->manager->can('delete', $initiative))->toBeFalse();
});

it('scopes the association initiative list to the current user organization', function () {
    $myInitiative = Initiative::factory()->for($this->org, 'organization')->create();

    $otherOrg = Organization::factory()->create(['type' => 'association']);
    $otherInitiative = Initiative::factory()->for($otherOrg, 'organization')->create();

    Livewire::test(ListInitiatives::class)
        ->assertCanSeeTableRecords([$myInitiative])
        ->assertCanNotSeeTableRecords([$otherInitiative]);
});
