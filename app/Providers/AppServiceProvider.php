<?php

namespace App\Providers;

use App\Filament\Widgets\AdminOperationsDashboardWidget;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\InitiativePayment;
use App\Models\MonthlyReport;
use App\Models\Organization;
use App\Models\ServiceEvaluation;
use App\Models\User;
use App\Models\VisitReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Models whose writes invalidate the admin dashboard cache.
     *
     * @var list<class-string<Model>>
     */
    private const DASHBOARD_MODELS = [
        Initiative::class,
        InitiativePayment::class,
        Consultation::class,
        VisitReport::class,
        MonthlyReport::class,
        ServiceEvaluation::class,
        Organization::class,
        User::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $forget = static function (Model $model): void {
            AdminOperationsDashboardWidget::forgetCache();
        };

        foreach (self::DASHBOARD_MODELS as $model) {
            $model::saved($forget);
            $model::deleted($forget);
        }

        // Mail + notification telemetry. Logs every send attempt so the
        // operator can verify mail traffic in storage/logs/laravel.log
        // without having to add ad-hoc dd() / dump() calls. Critical for
        // diagnosing "the email never arrived" reports on shared hosting.
        Event::listen(NotificationSending::class, function (NotificationSending $event): void {
            Log::info('Notification: sending', [
                'channel' => $event->channel,
                'notification' => $event->notification::class,
                'notifiable' => $event->notifiable::class,
                'notifiable_id' => method_exists($event->notifiable, 'getKey') ? $event->notifiable->getKey() : null,
                'route_mail' => method_exists($event->notifiable, 'routeNotificationForMail') ? $event->notifiable->routeNotificationForMail($event->notification) : null,
            ]);
        });

        Event::listen(NotificationSent::class, function (NotificationSent $event): void {
            Log::info('Notification: sent', [
                'channel' => $event->channel,
                'notification' => $event->notification::class,
                'notifiable_id' => method_exists($event->notifiable, 'getKey') ? $event->notifiable->getKey() : null,
            ]);
        });

        Event::listen(NotificationFailed::class, function (NotificationFailed $event): void {
            Log::error('Notification: FAILED', [
                'channel' => $event->channel,
                'notification' => $event->notification::class,
                'notifiable_id' => method_exists($event->notifiable, 'getKey') ? $event->notifiable->getKey() : null,
                'data' => $event->data,
            ]);
        });

        Event::listen(MessageSending::class, function (MessageSending $event): void {
            $to = collect($event->message->getTo() ?? [])->map(fn ($addr) => $addr->getAddress())->all();
            Log::info('Mail: sending', [
                'to' => $to,
                'subject' => $event->message->getSubject(),
                'mailer' => config('mail.default'),
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            $to = collect($event->message->getTo() ?? [])->map(fn ($addr) => $addr->getAddress())->all();
            Log::info('Mail: sent OK', [
                'to' => $to,
                'subject' => $event->message->getSubject(),
            ]);
        });
    }
}
