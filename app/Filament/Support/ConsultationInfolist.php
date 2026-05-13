<?php

namespace App\Filament\Support;

use App\Support\AttachmentLinks;
use App\Support\ConsultationOptions;
use App\Support\InitiativeSpecializations;
use App\Models\Consultation;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ConsultationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('consultations.sections.details'))
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->columns(2)
                ->schema([
                    TextEntry::make('subject')
                        ->label(__('consultations.fields.subject'))
                        ->weight('bold')
                        ->columnSpanFull(),
                    TextEntry::make('requesterOrganization.name_ar')
                        ->label(__('consultations.fields.requester_organization'))
                        ->placeholder('-'),
                    TextEntry::make('initiative.name_ar')
                        ->label(__('consultations.fields.initiative'))
                        ->placeholder('-'),
                    TextEntry::make('consultant.name')
                        ->label(__('consultations.fields.consultant'))
                        ->placeholder('-'),
                    TextEntry::make('responsibleUser.name')
                        ->label(__('consultations.fields.responsible_user'))
                        ->placeholder('-'),
                    TextEntry::make('request_type')
                        ->label(__('consultations.fields.request_type'))
                        ->formatStateUsing(fn (?string $state): string => ConsultationOptions::requestTypeLabel($state))
                        ->badge(),
                    TextEntry::make('routing_target')
                        ->label(__('consultations.fields.routing_target'))
                        ->formatStateUsing(fn (?string $state): string => ConsultationOptions::routingTargetLabel($state))
                        ->badge(),
                    TextEntry::make('specialization')
                        ->label(__('consultations.fields.specialization'))
                        ->formatStateUsing(fn (?string $state): string => $state ? (InitiativeSpecializations::options()[$state] ?? $state) : '-')
                        ->badge(),
                    TextEntry::make('status')
                        ->label(__('consultations.fields.status'))
                        ->formatStateUsing(fn (string $state): string => __('consultations.statuses.'.$state))
                        ->badge(),
                    TextEntry::make('details')
                        ->label(__('consultations.fields.details'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('attachments')
                        ->label(__('consultations.fields.attachments'))
                        ->state(fn (Consultation $record) => AttachmentLinks::render($record->attachments))
                        ->html()
                        ->columnSpanFull(),
                ]),

            Section::make(__('consultations.sections.session'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->columns(2)
                ->schema([
                    TextEntry::make('scheduled_at')
                        ->label(__('consultations.fields.scheduled_at'))
                        ->dateTime('Y-m-d h:i A')
                        ->placeholder('-'),
                    TextEntry::make('meeting_provider')
                        ->label(__('consultations.fields.meeting_provider'))
                        ->placeholder('-')
                        ->badge(),
                    TextEntry::make('meeting_url')
                        ->label(__('consultations.fields.meeting_url'))
                        ->placeholder('-')
                        ->url(fn (?string $state): ?string => $state)
                        ->openUrlInNewTab()
                        ->copyable()
                        ->columnSpanFull(),
                    TextEntry::make('meeting_password')
                        ->label(__('consultations.fields.meeting_password'))
                        ->placeholder('-')
                        ->copyable(),
                    TextEntry::make('closed_at')
                        ->label(__('consultations.fields.closed_at'))
                        ->dateTime('Y-m-d h:i A')
                        ->placeholder('-'),
                ]),

            RepeatableEntry::make('notes')
                ->label(__('consultations.sections.notes'))
                ->schema([
                    TextEntry::make('user.name')
                        ->label(__('consultations.fields.note_author'))
                        ->placeholder('-')
                        ->badge(),
                    TextEntry::make('created_at')
                        ->label(__('consultations.fields.created_at'))
                        ->dateTime('Y-m-d h:i A'),
                    TextEntry::make('note')
                        ->label(__('consultations.fields.note'))
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('تقييمات الخدمة')
                ->icon(Heroicon::OutlinedSparkles)
                ->schema([
                    TextEntry::make('service_evaluations')
                        ->state(fn (Consultation $record) => ServiceEvaluationSummary::render('consultation', $record->id))
                        ->html()
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
