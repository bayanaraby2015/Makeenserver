<?php

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the registration page with the brand layout', function () {
    $this->get(route('register.association.show'))
        ->assertOk()
        ->assertSee(__('register.page_title'), false)
        ->assertSee(__('register.fields.org_name_ar'), false)
        ->assertSee(__('register.fields.manager_email'), false)
        ->assertSee('makeen-logo', false);
});

it('rejects an empty submission with validation errors', function () {
    $this->from(route('register.association.show'))
        ->post(route('register.association.store'), [])
        ->assertRedirect(route('register.association.show'))
        ->assertSessionHasErrors([
            'org_name_ar', 'license_number', 'license_authority',
            'city', 'org_phone', 'org_email',
            'manager_name', 'manager_phone', 'manager_email',
            'password', 'accept_terms',
        ]);

    expect(Organization::count())->toBe(0);
    expect(User::count())->toBe(0);
});

it('creates an organization, a user, and assigns the manager role on success', function () {
    $payload = validRegistrationPayload();

    $this->post(route('register.association.store'), $payload)
        ->assertRedirect(route('register.association.pending'));

    $org = Organization::firstWhere('email', $payload['org_email']);
    expect($org)->not->toBeNull()
        ->and($org->type)->toBe(config('makeen.organization_types.association'))
        ->and($org->status)->toBe('pending')
        ->and($org->name_ar)->toBe($payload['org_name_ar']);

    $user = User::firstWhere('email', $payload['manager_email']);
    expect($user)->not->toBeNull()
        ->and($user->status)->toBe('pending')
        ->and($user->primary_organization_id)->toBe($org->id)
        ->and($user->hasRole(config('makeen.roles.association_manager')))->toBeTrue();
});

it('rejects duplicate organization or user emails', function () {
    $payload = validRegistrationPayload();
    $this->post(route('register.association.store'), $payload)->assertRedirect();

    $this->from(route('register.association.show'))
        ->post(route('register.association.store'), $payload)
        ->assertSessionHasErrors(['org_email', 'manager_email']);

    expect(Organization::count())->toBe(1);
    expect(User::count())->toBe(1);
});

it('renders the post-registration pending page right after a successful submission', function () {
    $payload = validRegistrationPayload();

    $this->followingRedirects()
        ->post(route('register.association.store'), $payload)
        ->assertOk()
        ->assertSee(__('register.pending.title'), false)
        ->assertSee(__('register.pending.body'), false);
});

it('redirects the standalone pending page to the form when no registration is in progress', function () {
    $this->get(route('register.association.pending'))
        ->assertRedirect(route('register.association.show'));
});

function validRegistrationPayload(array $overrides = []): array
{
    return array_replace([
        'org_name_ar' => 'جمعية البر',
        'org_name_en' => 'Al Bir Charity',
        'license_number' => 'LIC-12345',
        'license_authority' => 'وزارة الموارد البشرية',
        'city' => 'الرياض',
        'address' => 'حي الملز، شارع الستين',
        'org_phone' => '0112345678',
        'org_email' => 'org@example.org',
        'website' => 'https://example.org',
        'manager_name' => 'أحمد المدير',
        'manager_phone' => '0501234567',
        'manager_email' => 'manager@example.org',
        'password' => 'StrongPass1',
        'password_confirmation' => 'StrongPass1',
        'accept_terms' => '1',
    ], $overrides);
}
