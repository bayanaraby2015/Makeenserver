<?php

namespace App\Filament\Consultant\Resources\VisitReports\Pages;

use App\Filament\Consultant\Resources\VisitReports\VisitReportResource;
use App\Models\Initiative;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVisitReport extends EditRecord
{
    protected static string $resource = VisitReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $initiative = Initiative::query()->find($data['initiative_id']);
        $data['organization_id'] = $initiative?->organization_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
