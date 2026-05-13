<?php

namespace App\Filament\Association\Resources\ServiceEvaluations\Pages;

use App\Filament\Association\Resources\ServiceEvaluations\ServiceEvaluationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateServiceEvaluation extends CreateRecord
{
    protected static string $resource = ServiceEvaluationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['evaluator_id'] = Auth::id();
        $data['organization_id'] = Auth::user()?->primary_organization_id;
        $data['evaluated_at'] = now();

        return $data;
    }
}
