<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class AttachmentLinks
{
    public static function render(mixed $attachments): HtmlString
    {
        if (is_string($attachments)) {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [$attachments];
        }

        if ($attachments === null || $attachments === []) {
            return new HtmlString('-');
        }

        $links = collect($attachments)
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->unique()
            ->map(function (string $path): string {
                $normalizedPath = static::normalizePath($path);
                $url = route('attachments.show', ['path' => $normalizedPath]);
                $name = basename($path);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);

                if ($isImage) {
                    return sprintf(
                        '<a href="%s" target="_blank" rel="noopener" style="display:inline-flex;flex-direction:column;gap:8px;max-width:240px;padding:10px;border:1px solid #e5eaf3;border-radius:12px;background:#fff;text-decoration:none;box-shadow:0 10px 24px rgba(43,53,79,.07)"><img src="%s" alt="" style="width:220px;height:150px;border-radius:8px;object-fit:cover;background:#f3f6fb"><span style="color:#283979;font-weight:800;line-height:1.5;word-break:break-all">%s</span></a>',
                        e($url),
                        e($url),
                        e($name),
                    );
                }

                return sprintf(
                    '<a href="%s" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5eaf3;border-radius:10px;background:#fff;color:#283979;font-weight:800;text-decoration:none;box-shadow:0 8px 18px rgba(43,53,79,.06)">%s</a>',
                    e($url),
                    e($name),
                );
            })
            ->implode(' ');

        return new HtmlString($links !== '' ? $links : '-');
    }

    private static function normalizePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        if (str_contains($path, '/')) {
            return $path;
        }

        foreach (['consultations', 'visit-reports', 'monthly-reports'] as $directory) {
            $candidate = $directory.'/'.$path;

            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }
}
