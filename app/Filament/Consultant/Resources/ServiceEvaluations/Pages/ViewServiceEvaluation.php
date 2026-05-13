<?php

namespace App\Filament\Consultant\Resources\ServiceEvaluations\Pages;

use App\Filament\Consultant\Resources\ServiceEvaluations\ServiceEvaluationResource;
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
