<?php

namespace App\Filament\Excellence\Resources\Initiatives\Tables;

use App\Support\DisplayNumber;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExcellenceInitiativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('initiatives.fields.name_ar'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('organization.name_ar')
                    ->label(__('initiatives.fields.organization'))
                    ->searchable(),

                TextColumn::make('domain')
                    ->label(__('initiatives.fields.domain'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.domains.'.$state)),

                TextColumn::make('grand_total')
                    ->label(__('initiatives.fields.grand_total'))
                    ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                    ->html()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revisions_requested' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('submitted_at')
                    ->label(__('initiatives.fields.submitted_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->options([
                        'submitted' => __('initiatives.statuses.submitted'),
                        'under_review' => __('initiatives.statuses.under_review'),
                        'approved' => __('initiatives.statuses.approved'),
                        'rejected' => __('initiatives.statuses.rejected'),
                        'revisions_requested' => __('initiatives.statuses.revisions_requested'),
                    ]),
                SelectFilter::make('domain')
                    ->label(__('initiatives.fields.domain'))
                    ->options([
                        'developmental_impact' => __('initiatives.domains.developmental_impact'),
                        'sustainability' => __('initiatives.domains.sustainability'),
                        'institutional_empowerment' => __('initiatives.domains.institutional_empowerment'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('evaluate')
                    ->label(__('initiatives.tabs.kpis'))
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->color('primary')
                    ->url(fn ($record): string => route('filament.excellence.resources.initiatives.evaluate', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
