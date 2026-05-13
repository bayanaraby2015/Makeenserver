<?php

namespace App\Filament\Excellence\Resources\Consultations\Pages;

use App\Filament\Excellence\Resources\Consultations\ConsultationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;

class ListConsultations extends \App\Filament\Resources\Consultations\Pages\ListConsultations
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
            CreateAction::make(),
        ];
    }
}
