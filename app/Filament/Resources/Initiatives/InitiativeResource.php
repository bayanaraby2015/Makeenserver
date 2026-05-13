<?php

namespace App\Filament\Resources\Initiatives;

use App\Filament\Resources\Initiatives\Pages\CreateInitiative;
use App\Filament\Resources\Initiatives\Pages\EditInitiative;
use App\Filament\Resources\Initiatives\Pages\ListInitiatives;
use App\Filament\Resources\Initiatives\Pages\PrintInitiative;
use App\Filament\Resources\Initiatives\Pages\TimelineInitiative;
use App\Filament\Resources\Initiatives\Pages\ViewInitiative;
use App\Filament\Resources\Initiatives\Schemas\InitiativeForm;
use App\Filament\Resources\Initiatives\Schemas\InitiativeInfolist;
use App\Filament\Resources\Initiatives\Tables\InitiativesTable;
use App\Models\Initiative;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InitiativeResource extends Resource
{
    protected static ?string $model = Initiative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 25;

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

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return InitiativeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InitiativeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InitiativesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['organization.members', 'evaluations.evaluator']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInitiatives::route('/'),
            'create' => CreateInitiative::route('/create'),
            'view' => ViewInitiative::route('/{record}'),
            'edit' => EditInitiative::route('/{record}/edit'),
            'timeline' => TimelineInitiative::route('/{record}/timeline'),
            'print' => PrintInitiative::route('/{record}/print'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
