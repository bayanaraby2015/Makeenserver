<?php

namespace App\Filament\Consultant\Resources\VisitReports\Pages;

use App\Filament\Consultant\Resources\VisitReports\VisitReportResource;
use App\Models\Initiative;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateVisitReport extends CreateRecord
{
    protected static string $resource = VisitReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $initiative = Initiative::query()->find($data['initiative_id']);

        $data['organization_id'] = $initiative?->organization_id;
        $data['consultant_user_id'] = Auth::id();

        return $data;
    }
}
