<?php

namespace App\Filament\Excellence\Resources\Consultations\Pages;

use App\Filament\Excellence\Resources\Consultations\ConsultationResource;
use App\Models\Consultation;

class CalendarConsultations extends \App\Filament\Resources\Consultations\Pages\CalendarConsultations
{
    protected static string $resource = ConsultationResource::class;

    protected function eventUrl(Consultation $consultation): string
    {
        return ConsultationResource::getUrl('view', ['record' => $consultation]);
    }
}
