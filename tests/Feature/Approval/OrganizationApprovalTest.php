<?php

use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Mail\OrganizationApprovedMail;
use App\Mail\OrganizationRejectedMail;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@test.local',
        'status' => 'active',
    ]);
    $this->admin->assignRole('super_admin');

    $this->actingAs($this->admin);

    $this->org = Organization::factory()->create([
        'type' => 'association',
        'status' => 'pending',
        'email' => 'org@test.local',
    ]);

    $this->manager = User::factory()->create([
        'name' => 'Org Manager',
        'email' => 'manager@test.local',
        'status' => 'pending',
        'primary_organization_id' => $this->org->id,
    ]);
    $this->manager->assignRole('association_manager');
});

it('approves a pending organization, activates its members, and sends an approval email', function () {
    Mail::fake();

    Livewire::test(ListOrganizations::class)
        ->callTableAction('approve', $this->org);

    $this->org->refresh();
    expect($this->org->status)->toBe('active')
        ->and($this->org->approved_at)->not->toBeNull()
        ->and($this->org->approved_by)->toBe($this->admin->id);

    $this->manager->refresh();
    expect($this->manager->status)->toBe('active');

    Mail::assertSent(OrganizationApprovedMail::class, fn ($mail) => $mail->hasTo('org@test.local'));
});

it('rejects a pending organization with a reason and sends a rejection email', function () {
    Mail::fake();

    Livewire::test(ListOrganizations::class)
        ->callTableAction('reject', $this->org, ['reason' => 'سجل تجاري منتهي الصلاحية']);

    $this->org->refresh();
    expect($this->org->status)->toBe('rejected')
        ->and($this->org->rejection_reason)->toBe('سجل تجاري منتهي الصلاحية')
        ->and($this->org->rejected_at)->not->toBeNull()
        ->and($this->org->rejected_by)->toBe($this->admin->id);

    Mail::assertSent(OrganizationRejectedMail::class, function ($mail) {
        return $mail->hasTo('org@test.local')
            && $mail->reason === 'سجل تجاري منتهي الصلاحية';
    });
});

it('hides approve/reject actions on already-active organizations', function () {
    $this->org->update(['status' => 'active']);

    Livewire::test(ListOrganizations::class)
        ->assertTableActionHidden('approve', $this->org)
        ->assertTableActionHidden('reject', $this->org);
});
