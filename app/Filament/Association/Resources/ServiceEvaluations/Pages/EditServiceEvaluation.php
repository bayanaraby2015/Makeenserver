<?php

namespace App\Filament\Association\Resources\ServiceEvaluations\Pages;

use App\Filament\Association\Resources\ServiceEvaluations\ServiceEvaluationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceEvaluation extends EditRecord
{
    protected static string $resource = ServiceEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
