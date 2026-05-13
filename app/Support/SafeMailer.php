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
        // Respect explicit per-mailable opt-in via the ShouldQueue contract.
        if ($mailable instanceof ShouldQueue) {
            return true;
        }

        // Fall back to the application's default queue connection. If the
        // operator configured a real queue (database/redis/sqs), use it;
        // otherwise send synchronously.
        $connection = config('queue.default');

        return is_string($connection) && $connection !== 'sync';
    }
}
