<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\ConsultationResource;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CreateConsultation extends CreateRecord
{
    protected static string $resource = ConsultationResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord()->fresh(['consultant', 'responsibleUser', 'initiative', 'requesterOrganization']);

        if ($record === null) {
            return;
        }

        $consultant = ConsultationRecipients::responsible($record)
            ->merge(ConsultationRecipients::consultant($record))
            ->unique('id');

        if ($consultant->isNotEmpty()) {
            NotificationFacade::send(
                $consultant,
                new ConsultationStatusNotification($record, 'assigned'),
            );
        }
    }
}
