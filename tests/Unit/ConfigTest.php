<?php

it('exposes makeen config defaults', function () {
    expect(config('makeen.vat_rate'))->toBe(0.15)
        ->and(config('makeen.initiative_duration_months'))->toBe(32)
        ->and(config('makeen.timezone'))->toBe('Asia/Riyadh');
});

it('lists canonical roles', function () {
    expect(config('makeen.roles'))
        ->toHaveKey('super_admin')
        ->toHaveKey('excellence_manager')
        ->toHaveKey('donor_admin')
        ->toHaveKey('consultant')
        ->toHaveKey('association_manager');
});
