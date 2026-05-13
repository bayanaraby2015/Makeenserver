<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->in('Feature', 'Unit');

pest()->use(RefreshDatabase::class)
    ->in('Feature/Database', 'Feature/Registration', 'Feature/Auth', 'Feature/Approval', 'Feature/Resources', 'Feature/Mail', 'Feature/Initiatives', 'Feature/Consultations');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function something()
{
    // ...
}
