<?php

namespace App\Filament\Association\Resources\Consultations\Pages;

use App\Filament\Association\Resources\Consultations\ConsultationResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use Filament\Actions\Action;
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
            Action::make('reply')
                ->label(__('consultations.actions.reply'))
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('primary')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Textarea::make('note')
                        ->label(__('consultations.fields.reply'))
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

                    $recipients = ConsultationRecipients::consultant($record)
                        ->merge(ConsultationRecipients::responsible($record))
                        ->unique('id')
                        ->values();

                    if ($recipients->isNotEmpty()) {
                        NotificationFacade::send(
                            $recipients,
                            new ConsultationStatusNotification($record, 'note_added'),
                        );
                    }

                    Notification::make()->success()->title(__('consultations.messages.reply_added'))->send();
                }),
        ];
    }
}
