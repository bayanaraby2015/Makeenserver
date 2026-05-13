<?php

namespace App\Filament\Consultant\Resources\Consultations;

use App\Filament\Consultant\Resources\Consultations\Pages\CalendarConsultations;
use App\Filament\Consultant\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Consultant\Resources\Consultations\Pages\ViewConsultation;
use App\Filament\Support\ConsultationInfolist;
use App\Models\Consultation;
use App\Support\ConsultationOptions;
use App\Support\InitiativeSpecializations;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $slug = 'consultations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('consultations.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('consultations.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('consultations.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConsultationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label(__('consultations.fields.subject'))
                    ->searchable(),
                TextColumn::make('requesterOrganization.name_ar')
                    ->label(__('consultations.fields.requester_organization')),
                TextColumn::make('request_type')
                    ->label(__('consultations.fields.request_type'))
                    ->formatStateUsing(fn (?string $state): string => ConsultationOptions::requestTypeLabel($state))
                    ->badge(),
                TextColumn::make('specialization')
                    ->label(__('consultations.fields.specialization'))
                    ->formatStateUsing(fn (?string $state): string => $state ? (InitiativeSpecializations::options()[$state] ?? $state) : '-')
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('consultations.fields.status'))
                    ->formatStateUsing(fn (string $state): string => __('consultations.statuses.'.$state))
                    ->badge(),
                TextColumn::make('scheduled_at')
                    ->label(__('consultations.fields.scheduled_at'))
                    ->dateTime('Y-m-d h:i A')
                    ->placeholder('-'),
                TextColumn::make('meeting_url')
                    ->label(__('consultations.fields.meeting_url'))
                    ->limit(28)
                    ->placeholder('-')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => __('consultations.statuses.requested'),
                        'accepted' => __('consultations.statuses.accepted'),
                        'rejected' => __('consultations.statuses.rejected'),
                        'scheduled' => __('consultations.statuses.scheduled'),
                        'completed' => __('consultations.statuses.completed'),
                        'cancelled' => __('consultations.statuses.cancelled'),
                    ]),
                SelectFilter::make('request_type')
                    ->label(__('consultations.fields.request_type'))
                    ->options(ConsultationOptions::requestTypes()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $specializations = Auth::user()?->consultantSpecializations()->pluck('specialization')->all() ?? [];

        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query
                    ->where('consultant_user_id', Auth::id())
                    ->orWhere('responsible_user_id', Auth::id())
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->whereNull('consultant_user_id')
                            ->where('status', 'requested');
                    });
            })
            ->where(function (Builder $query) use ($specializations): void {
                $query->where('consultant_user_id', Auth::id());
                $query->orWhere('responsible_user_id', Auth::id());

                if ($specializations === []) {
                    $query->orWhere(function (Builder $fallback): void {
                        $fallback
                            ->whereNull('consultant_user_id')
                            ->where('status', 'requested')
                            ->whereRaw('1 = 0');
                    });

                    return;
                }

                $query->orWhere(function (Builder $fallback) use ($specializations): void {
                    $fallback
                        ->whereNull('consultant_user_id')
                        ->where('status', 'requested')
                        ->whereIn('specialization', $specializations);
                });
            })
            ->with(['requesterOrganization', 'initiative', 'consultant', 'responsibleUser', 'notes.user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsultations::route('/'),
            'calendar' => CalendarConsultations::route('/calendar'),
            'view' => ViewConsultation::route('/{record}'),
        ];
    }
}
