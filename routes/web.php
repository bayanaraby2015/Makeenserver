<?php

use App\Http\Controllers\Public\AssociationRegistrationController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

/*
|--------------------------------------------------------------------------
| Public Association Self-Registration
|--------------------------------------------------------------------------
| Associations register themselves through these routes. The created
| Organization + User both start as status=pending and require admin
| approval before login is possible (anti-spam + verification).
|
| Consultants and donor staff are added from the super_admin panel only.
*/
Route::prefix('register')->name('register.')->group(function () {
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('association', [AssociationRegistrationController::class, 'show'])
            ->name('association.show');
        Route::get('association/pending', [AssociationRegistrationController::class, 'pending'])
            ->name('association.pending');
    });

    // Tighter throttle on the submission endpoint to deter spam / bot
    // registrations. Tests run with CACHE_STORE=array so counters reset per
    // process and the legitimate test suite stays comfortably under the cap.
    Route::middleware('throttle:20,10')->group(function () {
        Route::post('association', [AssociationRegistrationController::class, 'store'])
            ->name('association.store');
    });
});

Route::middleware('auth')->get('/attachments/{path}', [AttachmentController::class, 'show'])
    ->where('path', '.*')
    ->name('attachments.show');
