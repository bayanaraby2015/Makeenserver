<?php

use App\Filament\Association\Resources\Initiatives\Pages\ViewInitiative as AssociationViewInitiative;
use App\Filament\Consultant\Resources\Consultations\Pages\ViewConsultation;
use App\Notifications\ConsultationStatusNotification;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('consultant'));

    $this->associationOrg = Organization::factory()->create([
        'type' => 'association',
        'status' => 'active',
    ]);

    $this->associationManager = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->associationOrg->id,
    ]);
    $this->associationManager->assignRole('association_manager');

    $this->consultant = User::factory()->create(['status' => 'active']);
    $this->consultant->assignRole('consultant');
});

it('allows an association manager to create a consultation request', function () {
    $initiative = Initiative::factory()->for($this->associationOrg, 'organization')->approved()->create();

    $this->actingAs($this->associationManager);

    $consultation = Consultation::query()->create([
        'requester_organization_id' => $this->associationOrg->id,
        'initiative_id' => $initiative->id,
        'specialization' => 'financial',
        'subject' => 'Need budget restructuring support',
        'details' => 'Please review budget assumptions and phasing.',
        'status' => 'requested',
        'requested_at' => now(),
    ]);

    expect($consultation->status)->toBe('requested')
        ->and($consultation->requester_organization_id)->toBe($this->associationOrg->id);
});

it('notifies the consultation department when an association requests consultation from an initiative', function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('association'));

    $superAdmin = User::factory()->create(['status' => 'active']);
    $superAdmin->assignRole('super_admin');

    $initiative = Initiative::factory()->for($this->associationOrg, 'organization')->approved()->create();

    $this->actingAs($this->associationManager);

    Livewire::test(AssociationViewInitiative::class, ['record' => $initiative->getRouteKey()])
        ->callAction('request_consultation', [
            'specialization' => 'financial',
            'subject' => 'Need budget restructuring support',
            'details' => 'Please review budget assumptions and phasing.',
        ]);

    Notification::assertSentTo($superAdmin, ConsultationStatusNotification::class);
    Notification::assertSentTo($this->consultant, ConsultationStatusNotification::class);

    expect(Consultation::query()->where('initiative_id', $initiative->id)->exists())->toBeTrue();
});

it('lets assigned consultant accept, schedule, and complete a consultation', function () {
    $consultation = Consultation::query()->create([
        'requester_organization_id' => $this->associationOrg->id,
        'consultant_user_id' => $this->consultant->id,
        'specialization' => 'operational',
        'subject' => 'Ops consultation',
        'status' => 'requested',
        'requested_at' => now(),
    ]);

    $this->actingAs($this->consultant);

    Livewire::test(ViewConsultation::class, ['record' => $consultation->getRouteKey()])
        ->callAction('accept');

    $consultation->refresh();
    expect($consultation->status)->toBe('accepted');

    Livewire::test(ViewConsultation::class, ['record' => $consultation->getRouteKey()])
        ->callAction('schedule', ['scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);

    $consultation->refresh();
    expect($consultation->status)->toBe('scheduled')
        ->and($consultation->scheduled_at)->not->toBeNull();

    Livewire::test(ViewConsultation::class, ['record' => $consultation->getRouteKey()])
        ->callAction('complete');

    $consultation->refresh();
    expect($consultation->status)->toBe('completed')
        ->and($consultation->closed_at)->not->toBeNull();
});
