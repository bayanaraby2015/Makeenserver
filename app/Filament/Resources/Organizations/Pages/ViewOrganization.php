<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewOrganization extends ViewRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('organizations.actions.approve'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (Organization $record): bool => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => __('organizations.actions.approve_modal_heading'))
                ->modalDescription(fn (): string => __('organizations.actions.approve_modal_description'))
                ->action(function (Organization $record): void {
                    Log::info('OrganizationApprove: header action (view page) triggered', [
                        'organization_id' => $record->id,
                        'approved_by' => Auth::id(),
                    ]);

                    $record->approveBy(Auth::id());

                    Notification::make()
                        ->success()
                        ->title(__('organizations.actions.approve_success'))
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
