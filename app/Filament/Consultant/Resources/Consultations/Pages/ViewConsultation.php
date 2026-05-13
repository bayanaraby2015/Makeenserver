<?php

namespace App\Filament\Consultant\Resources\Consultations\Pages;

use App\Filament\Consultant\Resources\Consultations\ConsultationResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use App\Support\ZoomMeetingScheduler;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ViewConsultation extends ViewRecord
{
    protected static string $resource = ConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('consultation', fn (Consultation $record): ?int => $record->requester_organization_id),
            Action::make('accept')
                ->label(__('consultations.actions.accept'))
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->visible(fn (Consultation $record): bool => $record->status === 'requested')
                ->action(function (Consultation $record): void {
                    $record->update([
                        'consultant_user_id' => $record->consultant_user_id ?? Auth::id(),
                        'status' => 'accepted',
                        'proposed_at' => now(),
                    ]);

                    $associationUsers = ConsultationRecipients::associationUsers($record);
                    $internalUsers = ConsultationRecipients::responsible($record);
                    $recipients = $associationUsers->merge($internalUsers)->unique('id')->values();
                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'accepted'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.accepted'))->send();
                }),
            Action::make('reject')
                ->label(__('consultations.actions.reject'))
                ->icon(Heroicon::OutlinedXMark)
                ->color('danger')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Textarea::make('rejection_reason')
                        ->label(__('consultations.fields.rejection_reason'))
                        ->required()
                        ->rows(3),
                ]))
                ->visible(fn (Consultation $record): bool => in_array($record->status, ['requested', 'accepted'], true))
                /** @param array{rejection_reason: string} $data */
                ->action(function (Consultation $record, array $data): void {
                    $record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'closed_at' => now(),
                    ]);

                    $associationUsers = ConsultationRecipients::associationUsers($record);
                    $recipients = $associationUsers->merge(ConsultationRecipients::responsible($record))->unique('id')->values();
                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'rejected'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.rejected'))->send();
                }),
            Action::make('schedule')
                ->label(__('consultations.actions.schedule'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    DateTimePicker::make('scheduled_at')
                        ->label(__('consultations.fields.scheduled_at'))
                        ->required()
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('Y-m-d h:i A')
                        ->default(fn (Consultation $record): mixed => $record->scheduled_at),
                    Toggle::make('create_zoom_meeting')
                        ->label(__('consultations.fields.create_zoom_meeting'))
                        ->default(app(ZoomMeetingScheduler::class)->isConfigured())
                        ->visible(fn (): bool => app(ZoomMeetingScheduler::class)->isConfigured()),
                    TextInput::make('meeting_url')
                        ->label(__('consultations.fields.meeting_url'))
                        ->url()
                        ->maxLength(255)
                        ->helperText(__('consultations.messages.manual_meeting_url')),
                ]))
                ->visible(fn (Consultation $record): bool => in_array($record->status, ['accepted', 'scheduled'], true))
                /** @param array{scheduled_at: string, create_zoom_meeting?: bool, meeting_url?: string|null} $data */
                ->action(function (Consultation $record, array $data): void {
                    $zoomMeeting = null;

                    if (($data['create_zoom_meeting'] ?? false) === true) {
                        $zoomMeeting = app(ZoomMeetingScheduler::class)->create($record, $data['scheduled_at']);
                    }

                    $record->update([
                        'status' => 'scheduled',
                        'scheduled_at' => $data['scheduled_at'],
                        'meeting_provider' => $zoomMeeting['provider'] ?? (filled($data['meeting_url'] ?? null) ? 'manual' : $record->meeting_provider),
                        'meeting_id' => $zoomMeeting['meeting_id'] ?? $record->meeting_id,
                        'meeting_url' => $zoomMeeting['join_url'] ?? ($data['meeting_url'] ?? $record->meeting_url),
                        'meeting_password' => $zoomMeeting['password'] ?? $record->meeting_password,
                    ]);

                    $associationUsers = ConsultationRecipients::associationUsers($record);
                    $recipients = $associationUsers->merge(ConsultationRecipients::responsible($record))->unique('id')->values();
                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'scheduled'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.scheduled'))->send();
                }),
            Action::make('complete')
                ->label(__('consultations.actions.complete'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('gray')
                ->visible(fn (Consultation $record): bool => in_array($record->status, ['accepted', 'scheduled'], true))
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Textarea::make('note')
                        ->label(__('consultations.fields.closing_note'))
                        ->helperText(__('consultations.messages.closing_note_help'))
                        ->required()
                        ->rows(4),
                ]))
                /** @param array{note: string} $data */
                ->action(function (Consultation $record, array $data): void {
                    ConsultationNote::query()->create([
                        'consultation_id' => $record->id,
                        'user_id' => (int) Auth::id(),
                        'note' => $data['note'],
                        'visibility' => 'shared',
                    ]);

                    $record->update([
                        'status' => 'completed',
                        'closed_at' => now(),
                    ]);

                    $associationUsers = ConsultationRecipients::associationUsers($record);
                    $recipients = $associationUsers->merge(ConsultationRecipients::responsible($record))->unique('id')->values();
                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'completed'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.completed'))->send();
                }),
            Action::make('add_note')
                ->label(__('consultations.actions.add_note'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Textarea::make('note')
                        ->label(__('consultations.fields.note'))
                        ->required()
                        ->rows(3),
                ]))
                /** @param array{note: string} $data */
                ->action(function (Consultation $record, array $data): void {
                    ConsultationNote::query()->create([
                        'consultation_id' => $record->id,
                        'user_id' => (int) Auth::id(),
                        'note' => $data['note'],
                        'visibility' => 'shared',
                    ]);

                    $associationUsers = ConsultationRecipients::associationUsers($record);
                    $recipients = $associationUsers->merge(ConsultationRecipients::responsible($record))->unique('id')->values();
                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'note_added'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.note_added'))->send();
                }),
        ];
    }
}
