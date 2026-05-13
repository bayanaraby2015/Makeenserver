<?php

namespace App\Filament\Excellence\Resources\Initiatives;

use App\Filament\Excellence\Resources\Initiatives\Pages\CreateInitiative;
use App\Filament\Excellence\Resources\Initiatives\Pages\EditInitiative;
use App\Filament\Excellence\Resources\Initiatives\Pages\EvaluateInitiative;
use App\Filament\Excellence\Resources\Initiatives\Pages\ListInitiatives;
use App\Filament\Excellence\Resources\Initiatives\Pages\TimelineInitiative;
use App\Filament\Excellence\Resources\Initiatives\Pages\ViewInitiative;
use App\Filament\Excellence\Resources\Initiatives\Tables\ExcellenceInitiativesTable;
use App\Filament\Resources\Initiatives\Schemas\InitiativeForm;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

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

    public static function table(Table $table): Table
    {
        return ExcellenceInitiativesTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return InitiativeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InitiativeInfolist::configure($schema);
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
            'evaluate' => EvaluateInitiative::route('/{record}/evaluate'),
        ];
    }
}
