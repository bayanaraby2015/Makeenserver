<?php

namespace App\Filament\Association\Resources\Initiatives\Pages;

use App\Filament\Association\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use App\Notifications\InitiativeReviewedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Spatie\Permission\Models\Role;

class EditInitiative extends EditRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label(__('initiatives.actions.submit'))
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('primary')
                ->visible(fn (Initiative $record): bool => in_array($record->status, ['draft', 'revisions_requested'], true))
                ->requiresConfirmation()
                ->modalHeading(__('initiatives.actions.submit_modal_heading'))
                ->modalDescription(__('initiatives.actions.submit_modal_description'))
                ->action(function (Initiative $record): void {
                    $record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    /** @var Role|null $adminRole */
                    $adminRole = Role::query()->where('name', 'super_admin')->first();
                    if ($adminRole !== null) {
                        $admins = $adminRole->users()->get();
                        if ($admins->isNotEmpty()) {
                            NotificationFacade::send(
                                $admins,
                                new InitiativeReviewedNotification($record, 'submitted'),
                            );
                        }
                    }

                    Notification::make()
                        ->success()
                        ->title(__('initiatives.actions.submit_success'))
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn (Initiative $record): bool => $record->status === 'draft'),
        ];
    }
}
