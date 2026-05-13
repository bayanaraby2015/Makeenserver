<?php

namespace App\Filament\Excellence\Resources\Consultations;

use App\Filament\Excellence\Resources\Consultations\Pages\CalendarConsultations;
use App\Filament\Excellence\Resources\Consultations\Pages\CreateConsultation;
use App\Filament\Excellence\Resources\Consultations\Pages\EditConsultation;
use App\Filament\Excellence\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Excellence\Resources\Consultations\Pages\ViewConsultation;

class ConsultationResource extends \App\Filament\Resources\Consultations\ConsultationResource
{
    protected static ?string $slug = 'consultations';

    protected static ?int $navigationSort = 20;

    public static function getPages(): array
    {
        return [
            'index' => ListConsultations::route('/'),
            'create' => CreateConsultation::route('/create'),
            'calendar' => CalendarConsultations::route('/calendar'),
            'view' => ViewConsultation::route('/{record}'),
            'edit' => EditConsultation::route('/{record}/edit'),
        ];
    }
}
