<?php

namespace App\Notifications;

use App\Models\DonorInterest;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DonorInterestNotification extends Notification
{
    use Queueable;

    public function __construct(public DonorInterest $interest) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): DatabaseMessage
    {
        $initiativeName = $this->interest->initiative->name_ar ?? '';
        $donorName = $this->interest->user->name ?? '';
        $initiativeId = (int) ($this->interest->initiative->id ?? 0);

        return new DatabaseMessage(
            FilamentNotification::make()
                ->title(__('notifications.donor_interest.title', ['initiative' => $initiativeName]))
                ->body(__('notifications.donor_interest.body', ['donor' => $donorName]))
                ->status('info')
                ->actions([
                    Action::make('view')
                        ->label(__('initiatives.actions.view'))
                        ->url(url('/admin/initiatives/'.$initiativeId))
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
        );
    }
}
