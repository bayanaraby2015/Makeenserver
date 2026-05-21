<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('approve')
                ->label(__('organizations.actions.approve'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (Organization $record): bool => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => __('organizations.actions.approve_modal_heading'))
                ->modalDescription(fn (): string => __('organizations.actions.approve_modal_description'))
                ->action(function (Organization $record): void {
                    Log::info('OrganizationApprove: header action (edit page) triggered', [
                        'organization_id' => $record->id,
                        'approved_by' => Auth::id(),
                    ]);

                    $record->approveBy(Auth::id());

                    Notification::make()
                        ->success()
                        ->title(__('organizations.actions.approve_success'))
                        ->send();
                }),
            Action::make('suspend')
                ->label(__('organizations.actions.suspend'))
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (Organization $record): bool => $record->status === 'active')
                ->requiresConfirmation()
                ->action(function (Organization $record): void {
                    $record->update(['status' => 'suspended']);
                    foreach ($record->members as $member) {
                        $member->update(['status' => 'suspended']);
                    }
                    Notification::make()->danger()->title(__('organizations.actions.suspend_success'))->send();
                }),
            Action::make('reactivate')
                ->label(__('organizations.actions.reactivate'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (Organization $record): bool => $record->status === 'suspended')
                ->requiresConfirmation()
                ->action(function (Organization $record): void {
                    $record->update(['status' => 'active']);
                    foreach ($record->members as $member) {
                        $member->update(['status' => 'active']);
                    }
                    Notification::make()->success()->title(__('organizations.actions.reactivate_success'))->send();
                }),
        ];
    }
}
