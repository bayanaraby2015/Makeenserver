<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
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
            Action::make('add_note')
                ->label(__('consultations.actions.add_note'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Textarea::make('note')
                        ->label(__('consultations.fields.note'))
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

                    $recipients = ConsultationRecipients::associationUsers($record)
                        ->merge(ConsultationRecipients::consultant($record))
                        ->merge(ConsultationRecipients::responsible($record))
                        ->unique('id')
                        ->values();

                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send($recipients, new ConsultationStatusNotification($record, 'note_added'));
                    }

                    Notification::make()->success()->title(__('consultations.messages.note_added'))->send();
                }),
            EditAction::make(),
        ];
    }
}
