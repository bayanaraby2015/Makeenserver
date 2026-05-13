<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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
