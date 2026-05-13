<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('organizations.sections.identity'))
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label(__('organizations.fields.type'))
                            ->options([
                                'association' => __('organizations.types.association'),
                                'donor' => __('organizations.types.donor'),
                                'excellence_team' => __('organizations.types.excellence_team'),
                                'consultant_firm' => __('organizations.types.consultant_firm'),
                            ])
                            ->native(false)
                            ->required(),

                        Select::make('status')
                            ->label(__('organizations.fields.status'))
                            ->options([
                                'pending' => __('organizations.statuses.pending'),
                                'active' => __('organizations.statuses.active'),
                                'suspended' => __('organizations.statuses.suspended'),
                                'archived' => __('organizations.statuses.archived'),
                                'rejected' => __('organizations.statuses.rejected'),
                            ])
                            ->native(false)
                            ->required()
                            ->default('pending'),

                        TextInput::make('name_ar')
                            ->label(__('organizations.fields.name_ar'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_en')
                            ->label(__('organizations.fields.name_en'))
                            ->maxLength(255),
                    ]),

                Section::make(__('organizations.sections.license'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('license_number')
                            ->label(__('organizations.fields.license_number'))
                            ->maxLength(255),

                        TextInput::make('license_authority')
                            ->label(__('organizations.fields.license_authority'))
                            ->maxLength(255),
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
