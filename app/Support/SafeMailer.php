<?php

namespace App\Support;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends mail without letting a transport failure (bad SMTP creds,
 * unreachable host, invalid recipient) bubble up and break the
 * user-facing action.
 *
 * When the configured queue connection is not "sync" the mailable is
 * dispatched onto the queue so HTTP requests aren't blocked on SMTP.
 * Otherwise the mail is sent synchronously (useful in tests and during
 * local development). Every attempt is logged so failures can be
 * investigated post-hoc.
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
            $pendingMail = Mail::to($recipient);

            if (self::shouldQueue($mailable)) {
                $pendingMail->queue($mailable);

                Log::info('SafeMailer: queued', [
                    'recipient' => $recipient,
                    'mail' => $mailable::class,
                    'context' => $context,
                ]);
            } else {
                $pendingMail->send($mailable);

                Log::info('SafeMailer: sent', [
                    'recipient' => $recipient,
                    'mail' => $mailable::class,
                    'context' => $context,
                ]);
            }

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

    private static function shouldQueue(Mailable $mailable): bool
    {
        // Only queue when the mailable explicitly opts in. Sending
        // synchronously by default avoids "email never arrived" reports
        // on servers where the queue worker (php artisan queue:work)
        // isn't running — a very common production misconfiguration.
        return $mailable instanceof ShouldQueue;
    }
}
