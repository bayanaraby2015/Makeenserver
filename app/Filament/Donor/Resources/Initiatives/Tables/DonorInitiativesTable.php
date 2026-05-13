<?php

namespace App\Filament\Donor\Resources\Initiatives\Tables;

use App\Support\DisplayNumber;
use App\Support\InitiativeSpecializations;
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

                TextColumn::make('beneficiaries_scope')
                    ->label(__('initiatives.fields.beneficiaries_scope'))
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label(__('initiatives.fields.approved_at'))
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('specializations')
                    ->label(__('initiatives.fields.specializations'))
                    ->options(InitiativeSpecializations::options())
                    ->query(fn ($query, array $data) => isset($data['value']) && $data['value']
                        ? $query->whereJsonContains('specializations', $data['value'])
                        : $query),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
