<?php

use App\Filament\Resources\Initiatives\Pages\ListInitiatives;
use App\Mail\InitiativeApprovedMail;
use App\Mail\InitiativeRejectedMail;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\InitiativeReviewedNotification;
use Database\Seeders\KpiDefinitionsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(KpiDefinitionsSeeder::class);

    $this->admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@test.local',
        'status' => 'active',
    ]);
    $this->admin->assignRole('super_admin');

    $this->actingAs($this->admin);

    $this->org = Organization::factory()->create([
        'type' => 'association',
        'status' => 'active',
        'email' => 'assoc@test.local',
    ]);

    $this->manager = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->org->id,
    ]);
    $this->manager->assignRole('association_manager');

    $this->initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->submitted()
        ->create();
});

it('approves a submitted initiative, sends an email, and notifies the org manager', function () {
    Mail::fake();
    Notification::fake();

    Livewire::test(ListInitiatives::class)
        ->callTableAction('approve', $this->initiative);

    $this->initiative->refresh();
    expect($this->initiative->status)->toBe('approved')
        ->and($this->initiative->approved_at)->not->toBeNull()
        ->and($this->initiative->approved_by)->toBe($this->admin->id);

    Mail::assertSent(InitiativeApprovedMail::class, fn ($m) => $m->hasTo('assoc@test.local'));

    Notification::assertSentTo(
        $this->manager,
        InitiativeReviewedNotification::class,
        fn ($notification) => $notification->event === 'approved',
    );
});

it('rejects a submitted initiative with a reason, sends a rejection email, and notifies the manager', function () {
    Mail::fake();
    Notification::fake();

    $reason = 'يرجى مراجعة الميزانية والإطار الزمني.';

    Livewire::test(ListInitiatives::class)
        ->callTableAction('reject', $this->initiative, ['reason' => $reason]);

    $this->initiative->refresh();
    expect($this->initiative->status)->toBe('rejected')
        ->and($this->initiative->rejection_reason)->toBe($reason)
        ->and($this->initiative->rejected_at)->not->toBeNull();

    Mail::assertSent(
        InitiativeRejectedMail::class,
        fn ($m) => $m->hasTo('assoc@test.local') && $m->reason === $reason,
    );

    Notification::assertSentTo(
        $this->manager,
        InitiativeReviewedNotification::class,
        fn ($n) => $n->event === 'rejected' && $n->reason === $reason,
    );
});

it('hides approve/reject actions on draft initiatives', function () {
    $draft = Initiative::factory()->for($this->org, 'organization')->create(['status' => 'draft']);

    Livewire::test(ListInitiatives::class)
        ->assertTableActionHidden('approve', $draft)
        ->assertTableActionHidden('reject', $draft);
});

it('hides approve/reject actions on already-approved initiatives', function () {
    $approved = Initiative::factory()->for($this->org, 'organization')->approved()->create();

    Livewire::test(ListInitiatives::class)
        ->assertTableActionHidden('approve', $approved)
        ->assertTableActionHidden('reject', $approved);
});
