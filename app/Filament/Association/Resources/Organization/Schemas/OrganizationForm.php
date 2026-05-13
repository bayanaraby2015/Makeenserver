<?php

namespace App\Filament\Association\Resources\Organization\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Association-side organization form: identity & contact only.
 * Status, license number, type are read-only / hidden — only admins
 * can change those via the admin panel.
 */
class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('organizations.sections.identity'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_ar')
                            ->label(__('organizations.fields.name_ar'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_en')
                            ->label(__('organizations.fields.name_en'))
                            ->maxLength(255),

                        TextInput::make('license_number')
                            ->label(__('organizations.fields.license_number'))
                            ->disabled(),

                        TextInput::make('license_authority')
                            ->label(__('organizations.fields.license_authority'))
                            ->disabled(),
                    ]),

                Section::make(__('organizations.sections.contact'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('city')
                            ->label(__('organizations.fields.city'))
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('organizations.fields.phone'))
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label(__('organizations.fields.email'))
                            ->email()
                            ->maxLength(255),

                        TextInput::make('website')
                            ->label(__('organizations.fields.website'))
                            ->url()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label(__('organizations.fields.address'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
