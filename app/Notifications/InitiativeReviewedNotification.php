<?php

namespace App\Notifications;

use App\Models\Initiative;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InitiativeReviewedNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $event
     */
    public function __construct(
        public Initiative $initiative,
        public string $event,
        public ?string $reason = null,
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
        $title = match ($this->event) {
            'approved' => __('notifications.initiative.approved_title', ['name' => $this->initiative->name_ar]),
            'rejected' => __('notifications.initiative.rejected_title', ['name' => $this->initiative->name_ar]),
            'evaluated' => __('notifications.initiative.evaluated_title', ['name' => $this->initiative->name_ar]),
            'status_updated' => __('notifications.initiative.status_updated_title', ['name' => $this->initiative->name_ar]),
            default => __('notifications.initiative.submitted_title', ['name' => $this->initiative->name_ar]),
        };

        $body = match ($this->event) {
            'approved' => __('notifications.initiative.approved_body'),
            'rejected' => $this->reason ?? __('notifications.initiative.rejected_body'),
            'evaluated' => $this->reason ?? __('notifications.initiative.evaluated_body'),
            'status_updated' => $this->reason ?? __('notifications.initiative.status_updated_body'),
            default => __('notifications.initiative.submitted_body'),
        };

        $color = match ($this->event) {
            'approved' => 'success',
            'rejected' => 'danger',
            'evaluated' => 'info',
            'status_updated' => 'warning',
            default => 'warning',
        };

        $url = $this->resolveUrl($notifiable);

        return new DatabaseMessage(
            FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->status($color)
                ->actions([
                    Action::make('view')
                        ->label(__('initiatives.actions.view'))
                        ->url($url)
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mailTitle())
            ->markdown('mail.initiative.status', [
                'initiative' => $this->initiative,
                'title' => $this->mailTitle(),
                'body' => $this->mailBody(),
                'url' => $this->resolveUrl($notifiable),
                'reason' => $this->reason,
            ]);
    }

    protected function mailTitle(): string
    {
        return match ($this->event) {
            'approved' => __('notifications.initiative.approved_title', ['name' => $this->initiative->name_ar]),
            'rejected' => __('notifications.initiative.rejected_title', ['name' => $this->initiative->name_ar]),
            'evaluated' => __('notifications.initiative.evaluated_title', ['name' => $this->initiative->name_ar]),
            'status_updated' => __('notifications.initiative.status_updated_title', ['name' => $this->initiative->name_ar]),
            default => __('notifications.initiative.submitted_title', ['name' => $this->initiative->name_ar]),
        };
    }

    protected function mailBody(): string
    {
        return match ($this->event) {
            'approved' => __('notifications.initiative.approved_body'),
            'rejected' => $this->reason ?? __('notifications.initiative.rejected_body'),
            'evaluated' => $this->reason ?? __('notifications.initiative.evaluated_body'),
            'status_updated' => $this->reason ?? __('notifications.initiative.status_updated_body'),
            default => __('notifications.initiative.submitted_body'),
        };
    }

    /**
     * For admins (submitted event), link to /admin; otherwise to the panel
     * where the recipient (association manager) will read it.
     */
    protected function resolveUrl(mixed $notifiable): string
    {
        $id = (int) $this->initiative->id;

        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole(config('makeen.roles.consultant'))) {
            return url('/consultant/initiatives/'.$id);
        }

        if (method_exists($notifiable, 'hasAnyRole') && $notifiable->hasAnyRole([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.excellence_member'),
        ])) {
            return url('/admin/initiatives/'.$id);
        }

        return $this->event === 'submitted'
            ? url('/admin/initiatives/'.$id)
            : url('/association/initiatives/'.$id);
    }
}
