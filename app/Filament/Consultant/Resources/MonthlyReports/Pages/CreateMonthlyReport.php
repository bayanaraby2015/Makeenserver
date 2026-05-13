<?php

namespace App\Filament\Consultant\Resources\MonthlyReports\Pages;

use App\Filament\Consultant\Resources\MonthlyReports\MonthlyReportResource;
use App\Models\Initiative;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyReport extends CreateRecord
{
    protected static string $resource = MonthlyReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $initiative = Initiative::query()->find($data['initiative_id']);

        $data['organization_id'] = $initiative?->organization_id;
        $data['consultant_user_id'] = Auth::id();
        $data['submitted_at'] = ($data['status'] ?? null) === 'submitted' ? now() : null;

        return $data;
    }
}
