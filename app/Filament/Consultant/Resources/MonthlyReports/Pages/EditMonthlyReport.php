<?php

namespace App\Filament\Consultant\Resources\MonthlyReports\Pages;

use App\Filament\Consultant\Resources\MonthlyReports\MonthlyReportResource;
use App\Models\Initiative;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyReport extends EditRecord
{
    protected static string $resource = MonthlyReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $initiative = Initiative::query()->find($data['initiative_id']);

        $data['organization_id'] = $initiative?->organization_id;
        $data['submitted_at'] = ($data['status'] ?? null) === 'submitted'
            ? ($this->getRecord()->submitted_at ?? now())
            : $this->getRecord()->submitted_at;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
