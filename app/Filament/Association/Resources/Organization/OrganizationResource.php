<?php

namespace App\Filament\Association\Resources\Organization;

use App\Filament\Association\Resources\Organization\Pages\EditMyOrganization;
use App\Filament\Association\Resources\Organization\Schemas\OrganizationForm;
use App\Models\Organization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Association panel — read/edit the current user's primary organization only.
 *
 * The query is scoped to `Auth::user()->primary_organization_id` so an
 * association_manager cannot see or edit other associations.
 */
class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $slug = 'organization';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationLabel(): string
    {
        return __('organizations.model_label');
    }

    public static function getModelLabel(): string
    {
        return __('organizations.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('organizations.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $orgId = Auth::user()?->primary_organization_id;

        return parent::getEloquentQuery()->where('id', $orgId);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditMyOrganization::route('/'),
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
