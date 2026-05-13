<?php

namespace App\Filament\Consultant\Resources\Initiatives;

use App\Filament\Consultant\Resources\Initiatives\Pages\ListInitiatives;
use App\Filament\Consultant\Resources\Initiatives\Pages\ViewInitiative;
use App\Filament\Resources\Initiatives\Schemas\InitiativeInfolist;
use App\Models\Initiative;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InitiativeResource extends Resource
{
    protected static ?string $model = Initiative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationLabel(): string
    {
        return __('initiatives.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('initiatives.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('initiatives.plural_model_label');
    }

    public static function infolist(Schema $schema): Schema
    {
        return InitiativeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('initiatives.fields.name_ar'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('organization.name_ar')
                    ->label(__('initiatives.fields.organization'))
                    ->searchable(),
                TextColumn::make('specializations')
                    ->label(__('initiatives.fields.specializations'))
                    ->badge()
                    ->formatStateUsing(fn (mixed $state, Initiative $record): string => implode(', ', $record->specializationLabels()))
                    ->wrap(),
                TextColumn::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state)),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $specializations = Auth::user()?->consultantSpecializations()->pluck('specialization')->all() ?? [];

        // Note: only eager-load relations actually needed by the table
        // here. Heavy relations (outputs, milestones, payments, risks,
        // kpiValues, evaluations) are loaded on demand by the view/edit
        // pages instead.
        return parent::getEloquentQuery()
            ->with(['organization'])
            ->where(function (Builder $query) use ($specializations): void {
                if ($specializations === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                foreach ($specializations as $specialization) {
                    $query->orWhereJsonContains('specializations', $specialization)
                        ->orWhere(function (Builder $fallback) use ($specialization): void {
                            $fallback
                                ->whereNull('specializations')
                                ->where('domain', $specialization);
                        });
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInitiatives::route('/'),
            'view' => ViewInitiative::route('/{record}'),
        ];
    }
}
