<?php

namespace App\Filament\Association\Resources\Initiatives;

use App\Filament\Association\Resources\Initiatives\Pages\CreateInitiative;
use App\Filament\Association\Resources\Initiatives\Pages\EditInitiative;
use App\Filament\Association\Resources\Initiatives\Pages\ListInitiatives;
use App\Filament\Association\Resources\Initiatives\Pages\TimelineInitiative;
use App\Filament\Association\Resources\Initiatives\Pages\ViewInitiative;
use App\Filament\Association\Resources\Initiatives\Schemas\InitiativeWizardForm;
use App\Filament\Association\Resources\Initiatives\Tables\AssociationInitiativesTable;
use App\Filament\Resources\Initiatives\Schemas\InitiativeInfolist;
use App\Models\Initiative;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InitiativeResource extends Resource
{
    protected static ?string $model = Initiative::class;

    protected static ?string $slug = 'initiatives';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 20;

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

    public static function form(Schema $schema): Schema
    {
        return InitiativeWizardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssociationInitiativesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InitiativeInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $orgId = Auth::user()?->primary_organization_id;

        return parent::getEloquentQuery()
            ->where('organization_id', $orgId)
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
        ];
    }
}
