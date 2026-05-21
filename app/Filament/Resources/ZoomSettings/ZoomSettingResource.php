<?php

namespace App\Filament\Resources\ZoomSettings;

use App\Filament\Resources\ZoomSettings\Pages\CreateZoomSetting;
use App\Filament\Resources\ZoomSettings\Pages\EditZoomSetting;
use App\Filament\Resources\ZoomSettings\Pages\ListZoomSettings;
use App\Models\ZoomSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema as SchemaFacade;

class ZoomSettingResource extends Resource
{
    protected static ?string $model = ZoomSetting::class;

    protected static ?string $slug = 'zoom-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 90;

    public static function getNavigationGroup(): ?string
    {
        return __('zoom_settings.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('zoom_settings.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('zoom_settings.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('zoom_settings.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole('super_admin') === true
            && SchemaFacade::hasTable('zoom_settings');
    }

    public static function canCreate(): bool
    {
        return static::canViewAny() && ZoomSetting::query()->doesntExist();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('account_id')
                ->label(__('zoom_settings.fields.account_id'))
                ->maxLength(255),
            TextInput::make('client_id')
                ->label(__('zoom_settings.fields.client_id'))
                ->maxLength(255),
            TextInput::make('client_secret')
                ->label(__('zoom_settings.fields.client_secret'))
                ->password()
                ->revealable()
                ->maxLength(2000),
            TextInput::make('user_id')
                ->label(__('zoom_settings.fields.user_id'))
                ->helperText(__('zoom_settings.fields.user_id_help'))
                ->default('me')
                ->required()
                ->placeholder('me')
                ->maxLength(255),
            TextInput::make('default_duration')
                ->label(__('zoom_settings.fields.default_duration'))
                ->numeric()
                ->minValue(15)
                ->maxValue(240)
                ->default(60)
                ->suffix(__('zoom_settings.fields.minutes'))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('configured')
                    ->label(__('zoom_settings.fields.configured'))
                    ->boolean()
                    ->getStateUsing(fn (ZoomSetting $record): bool => $record->isConfigured()),
                TextColumn::make('account_id')
                    ->label(__('zoom_settings.fields.account_id'))
                    ->placeholder('-'),
                TextColumn::make('client_id')
                    ->label(__('zoom_settings.fields.client_id'))
                    ->placeholder('-'),
                TextColumn::make('user_id')
                    ->label(__('zoom_settings.fields.user_id')),
                TextColumn::make('default_duration')
                    ->label(__('zoom_settings.fields.default_duration'))
                    ->formatStateUsing(fn (int $state): string => $state.' '.__('zoom_settings.fields.minutes')),
                TextColumn::make('updated_at')
                    ->label(__('zoom_settings.fields.updated_at'))
                    ->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZoomSettings::route('/'),
            'create' => CreateZoomSetting::route('/create'),
            'edit' => EditZoomSetting::route('/{record}/edit'),
        ];
    }
}
