<?php

namespace App\Filament\Association\Resources\Consultations\Pages;

use App\Filament\Association\Resources\Consultations\ConsultationResource;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
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
        $data['requester_organization_id'] = Auth::user()?->primary_organization_id;
        $data['status'] = 'requested';
        $data['requested_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord()->fresh(['initiative', 'requesterOrganization', 'responsibleUser', 'consultant']);

        if ($record === null) {
            return;
        }

        $departmentUsers = ConsultationRecipients::responsible($record)
            ->merge(ConsultationRecipients::consultant($record))
            ->merge(ConsultationRecipients::consultationDepartment())
            ->unique('id');

        if ($departmentUsers->isNotEmpty()) {
            NotificationFacade::send(
                $departmentUsers,
                new ConsultationStatusNotification($record, 'requested'),
            );
        }
    }
}
