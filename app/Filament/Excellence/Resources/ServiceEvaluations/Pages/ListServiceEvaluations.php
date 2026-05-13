<?php

namespace App\Filament\Excellence\Resources\ServiceEvaluations\Pages;

use App\Filament\Excellence\Resources\ServiceEvaluations\ServiceEvaluationResource;
use App\Filament\Resources\ServiceEvaluations\Pages\ListServiceEvaluations as BaseListServiceEvaluations;

class ListServiceEvaluations extends BaseListServiceEvaluations
{
    protected static string $resource = ServiceEvaluationResource::class;
}
