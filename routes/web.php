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
    Route::get('association', [AssociationRegistrationController::class, 'show'])
        ->name('association.show');
    Route::post('association', [AssociationRegistrationController::class, 'store'])
        ->name('association.store');
    Route::get('association/pending', [AssociationRegistrationController::class, 'pending'])
        ->name('association.pending');
});

Route::middleware('auth')->get('/attachments/{path}', [AttachmentController::class, 'show'])
    ->where('path', '.*')
    ->name('attachments.show');
