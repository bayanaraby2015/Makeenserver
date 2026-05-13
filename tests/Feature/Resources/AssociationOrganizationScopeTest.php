<?php

use App\Filament\Association\Resources\Organization\Pages\EditMyOrganization;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Filament::setCurrentPanel(Filament::getPanel('association'));

    $this->ownOrg = Organization::factory()->active()->create([
        'type' => 'association',
        'name_ar' => 'جمعية المختبر',
    ]);

    $this->otherOrg = Organization::factory()->active()->create([
        'type' => 'association',
        'name_ar' => 'جمعية أخرى',
    ]);

    $this->manager = User::factory()->create([
        'status' => 'active',
        'primary_organization_id' => $this->ownOrg->id,
    ]);
    $this->manager->assignRole('association_manager');
});

it('shows the manager only their own organization on the panel page', function () {
    $this->actingAs($this->manager);

    Livewire::test(EditMyOrganization::class)
        ->assertFormSet([
            'name_ar' => 'جمعية المختبر',
        ]);
});

it('lets the manager update their own organization name', function () {
    $this->actingAs($this->manager);

    Livewire::test(EditMyOrganization::class)
        ->fillForm(['name_ar' => 'جمعية المختبر — محدّثة'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->ownOrg->fresh()->name_ar)->toBe('جمعية المختبر — محدّثة');
});
