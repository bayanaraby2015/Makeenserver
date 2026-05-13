<?php

namespace App\Filament\Donor\Resources\Initiatives;

use App\Filament\Donor\Resources\Initiatives\Pages\ListInitiatives;
use App\Filament\Donor\Resources\Initiatives\Pages\TimelineInitiative;
use App\Filament\Donor\Resources\Initiatives\Pages\ViewInitiative;
use App\Filament\Donor\Resources\Initiatives\Tables\DonorInitiativesTable;
use App\Filament\Resources\Initiatives\Schemas\InitiativeInfolist;
use App\Models\Initiative;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InitiativeResource extends Resource
{
    protected static ?string $model = Initiative::class;

    protected static ?string $slug = 'initiatives';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationLabel(): string
    {
        return __('donor.catalog_label');
    }

    public static function getModelLabel(): string
    {
        return __('initiatives.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('donor.catalog_label');
    }

    public static function table(Table $table): Table
    {
        return DonorInitiativesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InitiativeInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'approved');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInitiatives::route('/'),
            'view' => ViewInitiative::route('/{record}'),
            'timeline' => TimelineInitiative::route('/{record}/timeline'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
