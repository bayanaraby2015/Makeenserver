<?php

use App\Filament\Donor\Resources\Initiatives\Pages\ViewInitiative;
use App\Mail\DonorInterestMail;
use App\Models\DonorInterest;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\DonorInterestNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->donorOrg = Organization::factory()->create(['type' => 'donor']);

    $this->donor = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->donorOrg->id,
    ]);
    $this->donor->assignRole('donor_admin');

    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->assignRole('super_admin');

    $this->associationOrg = Organization::factory()->create([
        'type' => 'association',
        'status' => 'active',
        'email' => 'assoc@test.local',
    ]);

    $this->initiative = Initiative::factory()
        ->for($this->associationOrg, 'organization')
        ->approved()
        ->create();

    $this->actingAs($this->donor);
});

it('records donor interest, sends email to org, and notifies admins', function () {
    Mail::fake();
    Notification::fake();

    Livewire::test(ViewInitiative::class, ['record' => $this->initiative->getRouteKey()])
        ->callAction('express_interest', [
            'proposed_amount' => 50000,
            'message' => 'نتشرف بدعم المبادرة.',
        ]);

    $interest = DonorInterest::query()
        ->where('initiative_id', $this->initiative->id)
        ->where('user_id', $this->donor->id)
        ->first();

    expect($interest)->not->toBeNull();
    expect((float) $interest->proposed_amount)->toBe(50000.0);

    Mail::assertSent(DonorInterestMail::class, fn ($m) => $m->hasTo('assoc@test.local'));

    Notification::assertSentTo(
        $this->admin,
        DonorInterestNotification::class,
    );
});

it('hides express_interest action when donor already submitted interest', function () {
    DonorInterest::query()->create([
        'initiative_id' => $this->initiative->id,
        'user_id' => $this->donor->id,
        'donor_organization_id' => $this->donorOrg->id,
        'status' => 'pending',
    ]);

    Livewire::test(ViewInitiative::class, ['record' => $this->initiative->getRouteKey()])
        ->assertActionHidden('express_interest');
});
