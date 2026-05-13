<?php

namespace App\Filament\Association\Resources\Initiatives\Pages;

use App\Filament\Association\Resources\Initiatives\InitiativeResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Notifications\ConsultationStatusNotification;
use App\Support\ConsultationRecipients;
use App\Support\InitiativeSpecializations;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ViewInitiative extends ViewRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('initiative', fn (Initiative $record): ?int => $record->organization_id),
            Action::make('timeline')
                ->label(__('initiatives.actions.view_gantt'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(fn (): string => InitiativeResource::getUrl('timeline', ['record' => $this->getRecord()])),
            Action::make('request_consultation')
                ->label(__('consultations.actions.request_from_initiative'))
                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                ->color('primary')
                ->schema(fn (Schema $schema): Schema => $schema->components([
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
                        ->rows(3),
                ]))
                /** @param array{specialization: string, subject: string, details?: string|null} $data */
                ->action(function (Initiative $record, array $data): void {
                    $consultation = Consultation::query()->create([
                        'requester_organization_id' => Auth::user()?->primary_organization_id,
                        'initiative_id' => $record->id,
                        'specialization' => $data['specialization'],
                        'subject' => $data['subject'],
                        'details' => $data['details'] ?? null,
                        'status' => 'requested',
                        'requested_at' => now(),
                    ]);

                    $departmentUsers = ConsultationRecipients::consultationDepartment();

                    if ($departmentUsers->isNotEmpty()) {
                        NotificationFacade::send(
                            $departmentUsers,
                            new ConsultationStatusNotification($consultation, 'requested'),
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title(__('consultations.messages.created'))
                        ->send();
                }),
            EditAction::make()
                ->visible(fn (Initiative $record): bool => in_array($record->status, ['draft', 'revisions_requested'], true)),
        ];
    }
}
