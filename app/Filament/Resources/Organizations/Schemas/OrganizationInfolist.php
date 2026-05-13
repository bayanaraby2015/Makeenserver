<?php

namespace App\Filament\Resources\Organizations\Schemas;

use App\Models\Organization;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrganizationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('organizations.sections.identity'))
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name_ar')
                            ->label(__('organizations.fields.name_ar'))
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('name_en')
                            ->label(__('organizations.fields.name_en'))
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label(__('organizations.fields.type'))
                            ->formatStateUsing(fn (string $state): string => __('organizations.types.'.$state))
                            ->badge(),
                        TextEntry::make('status')
                            ->label(__('organizations.fields.status'))
                            ->formatStateUsing(fn (string $state): string => __('organizations.statuses.'.$state))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'active' => 'success',
                                'suspended' => 'danger',
                                'archived' => 'gray',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('members_count')
                            ->label(__('organizations.fields.members_count'))
                            ->getStateUsing(fn (Organization $record): int => $record->members()->count())
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('created_at')
                            ->label(__('organizations.fields.created_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                    ]),

                Section::make(__('organizations.sections.license'))
                    ->icon(Heroicon::OutlinedIdentification)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('license_number')
                            ->label(__('organizations.fields.license_number'))
                            ->placeholder('-'),
                        TextEntry::make('license_authority')
                            ->label(__('organizations.fields.license_authority'))
                            ->placeholder('-'),
                    ]),

                Section::make(__('organizations.sections.contact'))
                    ->icon(Heroicon::OutlinedPhone)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('city')
                            ->label(__('organizations.fields.city'))
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->label(__('organizations.fields.phone'))
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('email')
                            ->label(__('organizations.fields.email'))
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('website')
                            ->label(__('organizations.fields.website'))
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab(),
                        TextEntry::make('address')
                            ->label(__('organizations.fields.address'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('organizations.sections.lifecycle'))
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('approved_at')
                            ->label(__('organizations.fields.approved_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                        TextEntry::make('approver.name')
                            ->label(__('organizations.fields.approved_by'))
                            ->placeholder('-'),
                        TextEntry::make('rejected_at')
                            ->label(__('organizations.fields.rejected_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                        TextEntry::make('rejection_reason')
                            ->label(__('organizations.fields.rejection_reason'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
