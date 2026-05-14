<?php

namespace App\Filament\Association\Resources\Initiatives\Tables;

use App\Models\Initiative;
use App\Support\DisplayNumber;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssociationInitiativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('initiatives.fields.name_ar'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('specializations')
                    ->label(__('initiatives.fields.specializations'))
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => is_string($state) ? __('initiatives.specializations.'.$state) : '')
                    ->wrap(),

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
                        'draft' => 'gray',
                        'submitted', 'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revisions_requested' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('submitted_at')
                    ->label(__('initiatives.fields.submitted_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('initiatives.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->options([
                        'draft' => __('initiatives.statuses.draft'),
                        'submitted' => __('initiatives.statuses.submitted'),
                        'under_review' => __('initiatives.statuses.under_review'),
                        'approved' => __('initiatives.statuses.approved'),
                        'rejected' => __('initiatives.statuses.rejected'),
                        'revisions_requested' => __('initiatives.statuses.revisions_requested'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Initiative $record): bool => in_array($record->status, ['draft', 'revisions_requested'], true)),
                DeleteAction::make()
                    ->visible(fn (Initiative $record): bool => $record->status === 'draft'),
            ]);
    }
}
