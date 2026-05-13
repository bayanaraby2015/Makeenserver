<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\ConsultationResource;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class EditConsultation extends EditRecord
{
    protected static string $resource = ConsultationResource::class;

    protected ?int $originalConsultantUserId = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->originalConsultantUserId = $this->getRecord()->consultant_user_id;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->fresh(['consultant', 'initiative', 'requesterOrganization']);

        if ($record !== null && $record->consultant_user_id !== null && $record->consultant_user_id !== $this->originalConsultantUserId) {
            $consultant = ConsultationRecipients::consultant($record);

            if ($consultant->isNotEmpty()) {
                NotificationFacade::send(
                    $consultant,
                    new ConsultationStatusNotification($record, 'assigned'),
                );
            }
        }
    }
}
