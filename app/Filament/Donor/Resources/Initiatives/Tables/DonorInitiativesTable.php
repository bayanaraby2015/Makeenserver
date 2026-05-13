<?php

namespace App\Filament\Donor\Resources\Initiatives\Tables;

use App\Support\DisplayNumber;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DonorInitiativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('approved_at', 'desc')
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

                TextColumn::make('beneficiaries_scope')
                    ->label(__('initiatives.fields.beneficiaries_scope'))
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label(__('initiatives.fields.approved_at'))
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
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
            ]);
    }
}
