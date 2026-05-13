<?php

namespace App\Filament\Excellence\Resources\Initiatives\Pages;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
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
            EditAction::make(),
            ServiceEvaluationAction::make('initiative', fn (Initiative $record): ?int => $record->organization_id),
            Action::make('timeline')
                ->label(__('initiatives.actions.view_gantt'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('timeline', ['record' => $this->getRecord()])),
            Action::make('evaluate')
                ->label(__('initiatives.tabs.kpis'))
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('primary')
                ->url(fn (): string => static::getResource()::getUrl('evaluate', ['record' => $this->getRecord()])),
        ];
    }
}
