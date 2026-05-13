<?php

namespace App\Filament\Association\Resources\Consultations;

use App\Filament\Association\Resources\Consultations\Pages\CalendarConsultations;
use App\Filament\Association\Resources\Consultations\Pages\CreateConsultation;
use App\Filament\Association\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Association\Resources\Consultations\Pages\ViewConsultation;
use App\Filament\Support\ConsultationInfolist;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\User;
use App\Support\ConsultationOptions;
use App\Support\InitiativeSpecializations;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $slug = 'consultations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 30;

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
            Select::make('initiative_id')
                ->label(__('consultations.fields.initiative'))
                ->options(function (): array {
                    $orgId = Auth::user()?->primary_organization_id;

                    return Initiative::query()
                        ->where('organization_id', $orgId)
                        ->orderBy('name_ar')
                        ->pluck('name_ar', 'id')
                        ->all();
                })
                ->searchable(),
            Select::make('request_type')
                ->label(__('consultations.fields.request_type'))
                ->options(ConsultationOptions::requestTypes())
                ->required()
                ->default('question'),
            Select::make('routing_target')
                ->label(__('consultations.fields.routing_target'))
                ->options(ConsultationOptions::routingTargets())
                ->required()
                ->default('project_manager'),
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
                ->searchable()
                ->helperText(__('consultations.messages.responsible_user_help')),
            Select::make('specialization')
                ->label(__('consultations.fields.specialization'))
                ->required()
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
                ->disk('local')
                ->visibility('private')
                ->directory('consultations')
                ->downloadable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConsultationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('subject')
                ->label(__('consultations.fields.subject'))
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
            TextColumn::make('created_at')
                ->label(__('consultations.fields.created_at'))
                ->dateTime('Y-m-d h:i A'),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $orgId = Auth::user()?->primary_organization_id;

        return parent::getEloquentQuery()
            ->where('requester_organization_id', $orgId)
            ->with(['requesterOrganization', 'initiative', 'consultant', 'responsibleUser', 'notes.user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsultations::route('/'),
            'create' => CreateConsultation::route('/create'),
            'calendar' => CalendarConsultations::route('/calendar'),
            'view' => ViewConsultation::route('/{record}'),
        ];
    }
}
