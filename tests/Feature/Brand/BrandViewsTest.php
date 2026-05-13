<?php

it('renders alexandria font in brand head partial', function () {
    expect(view('brand.head')->render())
        ->toContain('Alexandria')
        ->toContain('fonts.googleapis.com');
});

it('renders both makeen and masar logos in dual-logo-stack', function () {
    $html = view('brand.dual-logo-stack')->render();
    expect($html)
        ->toContain('makeen-logo')
        ->toContain('masar-logo');
});

it('renders both makeen and masar logos in dual-logo header partial', function () {
    $html = view('brand.dual-logo')->render();
    expect($html)
        ->toContain('makeen-logo')
        ->toContain('masar-logo');
});

it('uses navy as the admin panel primary color', function () {
    expect(config('brand.panel_colors.admin'))->toBe('#283979');
});

it('renders dual logos and alexandria font on the admin login page', function () {
    $html = $this->get('/admin/login')->getContent();
    expect($html)
        ->toContain('Alexandria')
        ->toContain('makeen-logo')
        ->toContain('masar-logo');
});

it('renders dual logos in the brand area of every panel login page', function (string $panel) {
    $html = $this->get("/{$panel}/login")->getContent();
    expect($html)
        ->toContain('makeen-logo')
        ->toContain('masar-logo');
})->with(['admin', 'excellence', 'donor', 'consultant', 'association']);

it('uses navy override on Filament 4 primary color tokens for submit button', function () {
    $html = $this->get('/admin/login')->getContent();
    // Filament 4 uses --color-400/950 from --primary-* on .fi-color-primary
    expect($html)
        ->toContain('--color-400')
        ->toContain('fi-color-primary');
});

it('returns Arabic validation messages for missing registration fields', function () {
    $response = $this->post('/register/association', []);
    $response->assertSessionHasErrors([
        'org_name_ar', 'license_number', 'license_authority', 'city',
        'org_phone', 'org_email', 'manager_name', 'manager_phone',
        'manager_email', 'password', 'accept_terms',
    ]);

    $errors = $response->getSession()->get('errors')->getBag('default');
    foreach ($errors->messages() as $field => $msgs) {
        foreach ($msgs as $msg) {
            expect($msg)
                ->not->toContain('field is required')
                ->not->toContain('must be accepted');
        }
    }
});
