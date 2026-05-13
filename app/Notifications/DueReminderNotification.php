<?php

namespace App\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public string $status = 'warning',
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
        return new DatabaseMessage(
            FilamentNotification::make()
                ->title($this->title)
                ->body($this->body)
                ->status($this->status)
                ->actions([
                    Action::make('view')
                        ->label(__('initiatives.actions.view'))
                        ->url($this->url)
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->markdown('mail.reminders.due', [
                'title' => $this->title,
                'body' => $this->body,
                'url' => $this->url,
            ]);
    }
}
