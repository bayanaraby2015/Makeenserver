<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends mail synchronously but never lets a transport failure (bad SMTP creds,
 * unreachable host, invalid recipient) bubble up and break the user-facing action.
 * Every send attempt is logged so failures can be investigated post-hoc.
 */
class SafeMailer
{
    public static function send(?string $recipient, Mailable $mailable, string $context = ''): bool
    {
        if ($recipient === null || trim($recipient) === '' || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning('SafeMailer: skipped (invalid recipient)', [
                'recipient' => $recipient,
                'mail' => $mailable::class,
                'context' => $context,
            ]);

            return false;
        }

        try {
            Mail::to($recipient)->send($mailable);

            Log::info('SafeMailer: sent', [
                'recipient' => $recipient,
                'mail' => $mailable::class,
                'context' => $context,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('SafeMailer: send failed', [
                'recipient' => $recipient,
                'mail' => $mailable::class,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
