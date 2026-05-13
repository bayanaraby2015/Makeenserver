<?php

namespace App\Filament\Excellence\Resources\ServiceEvaluations;

use App\Filament\Excellence\Resources\ServiceEvaluations\Pages\ListServiceEvaluations;
use App\Filament\Excellence\Resources\ServiceEvaluations\Pages\ViewServiceEvaluation;
use App\Filament\Resources\ServiceEvaluations\ServiceEvaluationResource as BaseServiceEvaluationResource;

class ServiceEvaluationResource extends BaseServiceEvaluationResource
{
    protected static ?string $slug = 'service-evaluations';

    protected static ?int $navigationSort = 60;

    public static function getPages(): array
    {
        return [
            'index' => ListServiceEvaluations::route('/'),
            'view' => ViewServiceEvaluation::route('/{record}'),
        ];
    }
}
