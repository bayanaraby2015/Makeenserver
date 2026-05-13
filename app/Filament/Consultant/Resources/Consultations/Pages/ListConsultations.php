<?php

namespace App\Filament\Consultant\Resources\Consultations\Pages;

use App\Filament\Consultant\Resources\Consultations\ConsultationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListConsultations extends ListRecords
{
    protected static string $resource = ConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendar')
                ->label(__('consultations.actions.view_calendar'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(fn (): string => ConsultationResource::getUrl('calendar')),
        ];
    }
}
