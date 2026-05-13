<?php

namespace App\Notifications;

use App\Models\Consultation;
use App\Support\ConsultationOptions;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationStatusNotification extends Notification
{
    use Queueable;

    /**
     * @param  'requested'|'assigned'|'accepted'|'rejected'|'scheduled'|'completed'|'note_added'  $event
     */
    public function __construct(
        public Consultation $consultation,
        public string $event,
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
        $status = match ($this->event) {
            'requested' => 'warning',
            'rejected' => 'danger',
            'completed' => 'success',
            default => 'info',
        };

        return new DatabaseMessage(
            FilamentNotification::make()
                ->title($this->title())
                ->body($this->body())
                ->status($status)
                ->actions([
                    Action::make('view')
                        ->label(__('initiatives.actions.view'))
                        ->url($this->resolveUrl($notifiable))
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->markdown('mail.consultation.status', [
                'consultation' => $this->consultation,
                'title' => $this->title(),
                'body' => $this->body(),
                'url' => $this->resolveUrl($notifiable),
            ]);
    }

    protected function title(): string
    {
        return match ($this->event) {
            'requested' => __('consultations.messages.created'),
            'assigned' => __('consultations.messages.created'),
            'accepted' => __('consultations.messages.accepted'),
            'rejected' => __('consultations.messages.rejected'),
            'scheduled' => __('consultations.messages.scheduled'),
            'completed' => __('consultations.messages.completed'),
            default => __('consultations.messages.note_added'),
        };
    }

    protected function body(): string
    {
        $subject = $this->consultation->subject;
        $organization = $this->consultation->requesterOrganization?->name_ar;
        $initiative = $this->consultation->initiative?->name_ar;

        return collect([
            ConsultationOptions::requestTypeLabel($this->consultation->request_type),
            $subject,
            $initiative,
            $organization,
        ])->filter(fn (?string $value): bool => filled($value))->implode(' - ');
    }

    protected function resolveUrl(mixed $notifiable): string
    {
        $id = (int) $this->consultation->id;

        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('consultant')) {
            return url('/consultant/consultations/'.$id);
        }

        if (method_exists($notifiable, 'hasAnyRole') && $notifiable->hasAnyRole(['association_manager', 'association_member'])) {
            return url('/association/consultations/'.$id);
        }

        return url('/admin/consultations/'.$id);
    }
}
