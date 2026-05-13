<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\InitiativeSpecializations;
use App\Support\UserActivitySummary;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('users.sections.identity'))
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('users.fields.name'))
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('email')
                            ->label(__('users.fields.email'))
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label(__('users.fields.phone'))
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('status')
                            ->label(__('users.fields.status'))
                            ->formatStateUsing(fn (string $state): string => __('users.statuses.'.$state))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('locale')
                            ->label(__('users.fields.locale'))
                            ->formatStateUsing(fn (?string $state): string => $state === 'ar' ? __('users.locales.ar') : __('users.locales.en'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('email_verified_at')
                            ->label(__('users.fields.email_verified_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                    ]),

                Section::make(__('users.sections.access'))
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label(__('users.fields.roles'))
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(function (?string $state): string {
                                if ($state === null || $state === '') {
                                    return '-';
                                }

                                $key = 'roles.names.'.$state;
                                $translated = __($key);

                                return is_string($translated) && $translated !== $key ? $translated : $state;
                            }),
                        TextEntry::make('primaryOrganization.name_ar')
                            ->label(__('users.fields.primary_organization'))
                            ->placeholder('-')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('consultantSpecializations.specialization')
                            ->label(__('users.fields.consultant_specializations'))
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(function (?string $state): string {
                                if ($state === null || $state === '') {
                                    return '-';
                                }

                                return InitiativeSpecializations::options()[$state] ?? $state;
                            })
                            ->visible(fn (User $record): bool => $record->hasRole(config('makeen.roles.consultant')))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('users.sections.activity'))
                    ->icon(Heroicon::OutlinedClock)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('last_login_at')
                            ->label(__('users.fields.last_login_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                        TextEntry::make('last_login_ip')
                            ->label(__('users.fields.last_login_ip'))
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label(__('users.fields.created_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('users.fields.updated_at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label(__('users.fields.deleted_at'))
                            ->dateTime('Y-m-d H:i')
                            ->visible(fn (User $record): bool => $record->trashed()),
                    ]),

                Section::make(__('users.fields.recent_activity'))
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->schema([
                        TextEntry::make('recent_activity')
                            ->label('')
                            ->state(fn (User $record) => UserActivitySummary::render($record))
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
