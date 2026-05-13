<?php

namespace App\Filament\Resources\Consultations;

use App\Filament\Resources\Consultations\Pages\CalendarConsultations;
use App\Filament\Resources\Consultations\Pages\CreateConsultation;
use App\Filament\Resources\Consultations\Pages\EditConsultation;
use App\Filament\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Resources\Consultations\Pages\ViewConsultation;
use App\Filament\Support\ConsultationInfolist;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use App\Support\ConsultationOptions;
use App\Support\InitiativeSpecializations;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $slug = 'consultations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static ?int $navigationSort = 35;

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
        return $schema->components([
            Select::make('requester_organization_id')
                ->label(__('consultations.fields.requester_organization'))
                ->options(fn (): array => Organization::query()->orderBy('name_ar')->pluck('name_ar', 'id')->all())
                ->searchable()
                ->required(),
            Select::make('initiative_id')
                ->label(__('consultations.fields.initiative'))
                ->options(fn (): array => Initiative::query()->orderBy('name_ar')->pluck('name_ar', 'id')->all())
                ->searchable(),
            Select::make('consultant_user_id')
                ->label(__('consultations.fields.consultant'))
                ->options(fn (): array => User::role('consultant')->orderBy('name')->pluck('name', 'id')->all())
                ->searchable(),
            Select::make('responsible_user_id')
                ->label(__('consultations.fields.responsible_user'))
                ->options(fn (): array => User::query()
                    ->where('status', 'active')
                    ->role([
                        config('makeen.roles.excellence_manager'),
                        config('makeen.roles.excellence_member'),
                        config('makeen.roles.consultant'),
                    ])
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable(),
            Select::make('request_type')
                ->label(__('consultations.fields.request_type'))
                ->options(ConsultationOptions::requestTypes())
                ->required()
                ->default('consultation'),
            Select::make('routing_target')
                ->label(__('consultations.fields.routing_target'))
                ->options(ConsultationOptions::routingTargets())
                ->searchable(),
            Select::make('specialization')
                ->label(__('consultations.fields.specialization'))
                ->options(InitiativeSpecializations::options())
                ->searchable(),
            TextInput::make('subject')
                ->label(__('consultations.fields.subject'))
                ->required()
                ->maxLength(255),
            Textarea::make('details')
                ->label(__('consultations.fields.details'))
                ->rows(4),
            FileUpload::make('attachments')
                ->label(__('consultations.fields.attachments'))
                ->multiple()
                ->disk('public')
                ->directory('consultations')
                ->downloadable(),
            Select::make('status')
                ->label(__('consultations.fields.status'))
                ->options([
                    'requested' => __('consultations.statuses.requested'),
                    'accepted' => __('consultations.statuses.accepted'),
                    'rejected' => __('consultations.statuses.rejected'),
                    'scheduled' => __('consultations.statuses.scheduled'),
                    'completed' => __('consultations.statuses.completed'),
                    'cancelled' => __('consultations.statuses.cancelled'),
                ])
                ->required(),
            DateTimePicker::make('scheduled_at')
                ->label(__('consultations.fields.scheduled_at'))
                ->native(false)
                ->displayFormat('Y-m-d h:i A')
                ->seconds(false),
            Select::make('meeting_provider')
                ->label(__('consultations.fields.meeting_provider'))
                ->options([
                    'zoom' => 'Zoom',
                    'manual' => __('consultations.fields.meeting_url'),
                ]),
            TextInput::make('meeting_url')
                ->label(__('consultations.fields.meeting_url'))
                ->url()
                ->maxLength(255),
            TextInput::make('meeting_password')
                ->label(__('consultations.fields.meeting_password'))
                ->maxLength(255),
            Textarea::make('rejection_reason')
                ->label(__('consultations.fields.rejection_reason'))
                ->rows(3),
        ]);
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
                    ->label(__('consultations.fields.requester_organization'))
                    ->searchable(),
                TextColumn::make('consultant.name')
                    ->label(__('consultations.fields.consultant'))
                    ->placeholder('-'),
                TextColumn::make('request_type')
                    ->label(__('consultations.fields.request_type'))
                    ->formatStateUsing(fn (?string $state): string => ConsultationOptions::requestTypeLabel($state))
                    ->badge(),
                TextColumn::make('responsibleUser.name')
                    ->label(__('consultations.fields.responsible_user'))
                    ->placeholder('-'),
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
        return parent::getEloquentQuery()->with(['requesterOrganization', 'initiative', 'consultant', 'responsibleUser', 'notes.user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsultations::route('/'),
            'create' => CreateConsultation::route('/create'),
            'calendar' => CalendarConsultations::route('/calendar'),
            'view' => ViewConsultation::route('/{record}'),
            'edit' => EditConsultation::route('/{record}/edit'),
        ];
    }
}
