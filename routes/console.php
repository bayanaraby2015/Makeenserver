<?php

use App\Models\Consultation;
use App\Models\InitiativeMilestone;
use App\Models\InitiativePayment;
use App\Models\MonthlyReport;
use App\Models\User;
use App\Models\VisitReport;
use App\Notifications\DueReminderNotification;
use App\Support\ConsultationOptions;
use App\Support\ConsultationRecipients;
use App\Support\InitiativeRecipients;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('makeen:send-reminders {--days=7}', function (): int {
    $days = max(1, (int) $this->option('days'));
    $today = now()->startOfDay();
    $until = now()->addDays($days)->endOfDay();
    $sent = 0;

    $sendOnce = function (string $type, int $id, $recipients, string $title, string $body, string $targetType, int $targetId) use (&$sent): void {
        $recipients = collect($recipients)
            ->filter(fn (User $user): bool => $user->status === 'active')
            ->unique('id')
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $cacheKey = "makeen-reminder:{$type}:{$id}:".now()->toDateString();

        if (! Cache::add($cacheKey, true, now()->addDay())) {
            return;
        }

        foreach ($recipients as $recipient) {
            $panel = 'admin';

            if ($recipient->hasAnyRole([config('makeen.roles.association_manager'), config('makeen.roles.association_member')])) {
                $panel = 'association';
            } elseif ($recipient->hasRole(config('makeen.roles.consultant'))) {
                $panel = 'consultant';
            } elseif ($recipient->hasAnyRole([config('makeen.roles.excellence_manager'), config('makeen.roles.excellence_member')])) {
                $panel = 'excellence';
            }

            $path = match ($targetType) {
                'initiative' => 'initiatives',
                'visit-report' => 'visit-reports',
                'monthly-report' => 'monthly-reports',
                'consultation' => 'consultations',
                default => 'dashboard',
            };

            if ($targetType === 'monthly-report' && $panel === 'association') {
                $panel = 'admin';
            }

            if (in_array($targetType, ['visit-report', 'monthly-report'], true) && $panel === 'excellence') {
                $panel = 'admin';
            }

            Notification::send(
                $recipient,
                new DueReminderNotification($title, $body, url("/{$panel}/{$path}/{$targetId}")),
            );
        }

        $sent++;
    };

    $organizationUsers = fn (?int $organizationId) => $organizationId === null
        ? collect()
        : User::query()->where('primary_organization_id', $organizationId)->where('status', 'active')->get();

    $internalUsers = fn () => User::role([
        config('makeen.roles.super_admin'),
        config('makeen.roles.excellence_manager'),
        config('makeen.roles.excellence_member'),
    ])->where('status', 'active')->get();

    InitiativePayment::query()
        ->with('initiative')
        ->whereBetween('due_date', [$today->toDateString(), $until->toDateString()])
        ->chunkById(50, function ($payments) use ($sendOnce): void {
            foreach ($payments as $payment) {
                if ($payment->initiative === null) {
                    continue;
                }

                $sendOnce(
                    'payment',
                    (int) $payment->id,
                    InitiativeRecipients::relatedUsers($payment->initiative),
                    __('reminders.payment.title'),
                    __('reminders.payment.body', [
                        'initiative' => $payment->initiative->name_ar,
                        'date' => optional($payment->due_date)->format('Y-m-d'),
                    ]),
                    'initiative',
                    (int) $payment->initiative_id,
                );
            }
        });

    InitiativeMilestone::query()
        ->with('initiative')
        ->whereBetween('end_date', [$today->toDateString(), $until->toDateString()])
        ->chunkById(50, function ($milestones) use ($sendOnce): void {
            foreach ($milestones as $milestone) {
                if ($milestone->initiative === null) {
                    continue;
                }

                $sendOnce(
                    'milestone',
                    (int) $milestone->id,
                    InitiativeRecipients::relatedUsers($milestone->initiative),
                    __('reminders.milestone.title'),
                    __('reminders.milestone.body', [
                        'initiative' => $milestone->initiative->name_ar,
                        'phase' => $milestone->phase,
                        'date' => optional($milestone->end_date)->format('Y-m-d'),
                    ]),
                    'initiative',
                    (int) $milestone->initiative_id,
                );
            }
        });

    VisitReport::query()
        ->with(['initiative', 'organization', 'consultant'])
        ->whereIn('status', ['proposed', 'planned'])
        ->whereNotNull('scheduled_at')
        ->whereBetween('scheduled_at', [now(), $until])
        ->chunkById(50, function ($visits) use ($sendOnce, $organizationUsers): void {
            foreach ($visits as $visit) {
                $recipients = $organizationUsers($visit->organization_id)
                    ->merge($visit->consultant ? collect([$visit->consultant]) : collect())
                    ->unique('id')
                    ->values();

                $sendOnce(
                    'visit',
                    (int) $visit->id,
                    $recipients,
                    __('reminders.visit.title'),
                    __('reminders.visit.body', [
                        'initiative' => $visit->initiative?->name_ar ?? '-',
                        'date' => optional($visit->scheduled_at)->format('Y-m-d h:i A'),
                    ]),
                    'visit-report',
                    (int) $visit->id,
                );
            }
        });

    MonthlyReport::query()
        ->with(['initiative', 'organization', 'consultant'])
        ->whereIn('status', ['draft', 'submitted'])
        ->whereDate('report_month', '<=', $today->copy()->startOfMonth()->toDateString())
        ->chunkById(50, function ($reports) use ($sendOnce, $internalUsers): void {
            foreach ($reports as $report) {
                $recipients = $internalUsers()
                    ->merge($report->consultant ? collect([$report->consultant]) : collect())
                    ->unique('id')
                    ->values();

                $sendOnce(
                    'monthly-report',
                    (int) $report->id,
                    $recipients,
                    __('reminders.monthly_report.title'),
                    __('reminders.monthly_report.body', [
                        'initiative' => $report->initiative?->name_ar ?? '-',
                        'month' => optional($report->report_month)->format('Y-m'),
                    ]),
                    'monthly-report',
                    (int) $report->id,
                );
            }
        });

    Consultation::query()
        ->with(['requesterOrganization', 'initiative', 'consultant', 'responsibleUser'])
        ->whereIn('status', ['requested', 'accepted', 'scheduled'])
        ->where('updated_at', '<=', now()->subDay())
        ->chunkById(50, function ($consultations) use ($sendOnce): void {
            foreach ($consultations as $consultation) {
                $recipients = ConsultationRecipients::associationUsers($consultation)
                    ->merge(ConsultationRecipients::responsible($consultation))
                    ->merge(ConsultationRecipients::consultant($consultation))
                    ->unique('id')
                    ->values();

                $sendOnce(
                    'consultation',
                    (int) $consultation->id,
                    $recipients,
                    __('reminders.consultation.title'),
                    __('reminders.consultation.body', [
                        'type' => ConsultationOptions::requestTypeLabel($consultation->request_type),
                        'subject' => $consultation->subject,
                    ]),
                    'consultation',
                    (int) $consultation->id,
                );
            }
        });

    $this->info("Sent {$sent} reminder notification batches.");

    return 0;
})->purpose('Send Makeen reminders for plans, payments, visits, monthly reports, and open tickets.');
