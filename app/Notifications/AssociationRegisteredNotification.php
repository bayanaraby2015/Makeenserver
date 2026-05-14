<?php

namespace App\Notifications;

use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to all super_admin users when a new association self-registers
 * via the public /register/association page. Delivers both an in-app
 * (Filament bell) notification and an email so the admin team is
 * alerted without having to poll the dashboard.
 */
class AssociationRegisteredNotification extends Notification
{
    public function __construct(
        public Organization $organization,
        public ?string $managerName = null,
        public ?string $managerEmail = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(mixed $notifiable): DatabaseMessage
    {
        $title = 'تسجيل جمعية جديدة بانتظار المراجعة';
        $body = sprintf(
            'الجمعية: %s — المسؤول: %s — البريد: %s',
            $this->organization->name_ar ?? '—',
            $this->managerName ?? '—',
            $this->managerEmail ?? $this->organization->email ?? '—',
        );

        return new DatabaseMessage(
            FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->status('warning')
                ->actions([
                    Action::make('review')
                        ->label('مراجعة الطلب')
                        ->url(url('/admin/organizations/'.$this->organization->id))
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تسجيل جمعية جديدة - '.($this->organization->name_ar ?? ''))
            ->greeting('مرحباً،')
            ->line('تم تسجيل جمعية جديدة في المنصة وهي بانتظار مراجعتك واعتمادها.')
            ->line('اسم الجمعية: '.($this->organization->name_ar ?? '—'))
            ->line('المسؤول: '.($this->managerName ?? '—'))
            ->line('البريد الإلكتروني للمسؤول: '.($this->managerEmail ?? '—'))
            ->line('رقم الترخيص: '.($this->organization->license_number ?? '—'))
            ->line('المدينة: '.($this->organization->city ?? '—'))
            ->action('مراجعة الطلب الآن', url('/admin/organizations/'.$this->organization->id))
            ->line('شكراً لك.');
    }
}
