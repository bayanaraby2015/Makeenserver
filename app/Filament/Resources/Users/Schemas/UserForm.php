<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Organization;
use App\Support\InitiativeSpecializations;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('users.sections.identity'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('users.fields.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule): Unique => $rule->withoutTrashed())
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('users.fields.phone'))
                            ->tel()
                            ->maxLength(50),

                        Select::make('locale')
                            ->label(__('users.fields.locale'))
                            ->options([
                                'ar' => __('users.locales.ar'),
                                'en' => __('users.locales.en'),
                            ])
                            ->default('ar')
                            ->native(false),
                    ]),

                Section::make(__('users.sections.access'))
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label(__('users.fields.status'))
                            ->options([
                                'pending' => __('users.statuses.pending'),
                                'active' => __('users.statuses.active'),
                                'suspended' => __('users.statuses.suspended'),
                            ])
                            ->required()
                            ->default('active')
                            ->native(false),

                        Select::make('primary_organization_id')
                            ->label(__('users.fields.primary_organization'))
                            ->options(fn (): array => Organization::query()
                                ->orderBy('name_ar')
                                ->pluck('name_ar', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('roles')
                            ->label(__('users.fields.roles'))
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->options(function (): array {
                                $options = [];

                                foreach (Role::query()->orderBy('name')->get(['id', 'name']) as $role) {
                                    $name = (string) $role->name;
                                    $key = 'roles.names.'.$name;
                                    $translated = __($key);
                                    $options[$role->id] = is_string($translated) && $translated !== $key ? $translated : $name;
                                }

                                return $options;
                            })
                            ->preload()
                            ->columnSpanFull()
                            ->live()
                            ->native(false),

                        Select::make('consultant_specializations')
                            ->label(__('users.fields.consultant_specializations'))
                            ->multiple()
                            ->options(InitiativeSpecializations::options())
                            ->preload()
                            ->native(false)
                            ->dehydrated(false)
                            ->visible(function (Get $get): bool {
                                $consultantRoleId = Role::query()
                                    ->where('name', config('makeen.roles.consultant'))
                                    ->value('id');

                                return $consultantRoleId !== null
                                    && in_array((string) $consultantRoleId, array_map('strval', (array) $get('roles')), true);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make(__('users.sections.security'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label(__('users.fields.password'))
                            ->helperText(__('users.fields.password_help'))
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ]),
            ]);
    }
}
