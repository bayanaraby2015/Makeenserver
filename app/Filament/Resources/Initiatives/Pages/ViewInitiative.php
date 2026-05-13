<?php

namespace App\Filament\Resources\Initiatives\Pages;

use App\Filament\Resources\Initiatives\InitiativeResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Initiative;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewInitiative extends ViewRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('initiative', fn (Initiative $record): ?int => $record->organization_id),
            Action::make('timeline')
                ->label(__('initiatives.actions.view_gantt'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(fn (): string => InitiativeResource::getUrl('timeline', ['record' => $this->getRecord()])),
            Action::make('export_pdf')
                ->label(app()->isLocale('ar') ? 'تصدير PDF' : 'Export PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('primary')
                ->url(fn (): string => InitiativeResource::getUrl('print', ['record' => $this->getRecord()]))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
