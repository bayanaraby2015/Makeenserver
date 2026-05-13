<?php

namespace App\Filament\Association\Resources\ServiceEvaluations\Pages;

use App\Filament\Association\Resources\ServiceEvaluations\ServiceEvaluationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceEvaluation extends ViewRecord
{
    protected static string $resource = ServiceEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
