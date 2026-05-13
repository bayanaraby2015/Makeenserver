<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Tables\RolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('roles.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('roles.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('roles.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('roles.navigation_group');
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        // Sprint 2: roles are read-only. Editing UI lands in Sprint 3.
        return false;
    }
}
