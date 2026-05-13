<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController
{
    public function show(string $path): StreamedResponse|Response
    {
        abort_unless(
            str_starts_with($path, 'consultations/')
            || str_starts_with($path, 'visit-reports/')
            || str_starts_with($path, 'monthly-reports/'),
            404,
        );
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
