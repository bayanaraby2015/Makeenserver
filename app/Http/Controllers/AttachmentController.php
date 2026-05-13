<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\MonthlyReport;
use App\Models\User;
use App\Models\VisitReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController
{
    private const ALLOWED_DIRECTORIES = [
        'consultations',
        'visit-reports',
        'monthly-reports',
    ];

    public function show(Request $request, string $path): StreamedResponse|Response
    {
        // Block path traversal attempts (encoded or otherwise).
        $decodedPath = rawurldecode($path);

        if (str_contains($decodedPath, '..')
            || str_contains($decodedPath, "\0")
            || str_starts_with($decodedPath, '/')
        ) {
            abort(404);
        }

        $normalizedPath = ltrim($decodedPath, '/');
        $segments = explode('/', $normalizedPath);
        $directory = $segments[0] ?? '';

        if (! in_array($directory, self::ALLOWED_DIRECTORIES, true) || count($segments) < 2) {
            abort(404);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $this->authorizeOrAbort($user, $directory, $normalizedPath);

        $disk = Storage::disk('local');

        if (! $disk->exists($normalizedPath)) {
            // Fallback for legacy attachments that were stored on the public
            // disk before this controller was hardened. Authorization has
            // already been verified above.
            $publicDisk = Storage::disk('public');

            if ($publicDisk->exists($normalizedPath)) {
                return $publicDisk->response($normalizedPath);
            }

            abort(404);
        }

        return $disk->response($normalizedPath);
    }

    private function authorizeOrAbort(User $user, string $directory, string $path): void
    {
        $allowed = match ($directory) {
            'consultations' => $this->canAccessConsultationAttachment($user, $path),
            'visit-reports' => $this->canAccessVisitReportAttachment($user, $path),
            'monthly-reports' => $this->canAccessMonthlyReportAttachment($user, $path),
            default => false,
        };

        if (! $allowed) {
            abort(403);
        }
    }

    private function canAccessConsultationAttachment(User $user, string $path): bool
    {
        $consultation = Consultation::query()
            ->whereJsonContains('attachments', $path)
            ->first();

        if (! $consultation) {
            return false;
        }

        return $user->can('view', $consultation);
    }

    private function canAccessVisitReportAttachment(User $user, string $path): bool
    {
        $report = VisitReport::query()
            ->whereJsonContains('evidence_files', $path)
            ->first();

        if (! $report) {
            return false;
        }

        return $user->can('view', $report);
    }

    private function canAccessMonthlyReportAttachment(User $user, string $path): bool
    {
        $report = MonthlyReport::query()
            ->whereJsonContains('attachments', $path)
            ->first();

        if (! $report) {
            return false;
        }

        return $user->can('view', $report);
    }
}
