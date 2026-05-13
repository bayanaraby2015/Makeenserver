<?php

namespace App\Filament\Donor\Resources\Initiatives\Pages;

use App\Filament\Donor\Resources\Initiatives\InitiativeResource;
use App\Mail\DonorInterestMail;
use App\Models\DonorInterest;
use App\Models\Initiative;
use App\Models\User;
use App\Notifications\DonorInterestNotification;
use App\Support\SafeMailer;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Spatie\Permission\Models\Role;

class ViewInitiative extends ViewRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('timeline')
                ->label(__('initiatives.actions.view_gantt'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('timeline', ['record' => $this->getRecord()])),
            Action::make('express_interest')
                ->label(__('donor.actions.express_interest'))
                ->icon(Heroicon::OutlinedHandRaised)
                ->color('primary')
                ->visible(function (Initiative $record): bool {
                    $userId = Auth::id();
                    if ($userId === null) {
                        return false;
                    }

                    return ! DonorInterest::query()
                        ->where('initiative_id', $record->id)
                        ->where('user_id', $userId)
                        ->exists();
                })
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    TextInput::make('proposed_amount')
                        ->label(__('donor.fields.proposed_amount'))
                        ->numeric()
                        ->prefix('ر.س')
                        ->minValue(0),
                    Textarea::make('message')
                        ->label(__('donor.fields.message'))
                        ->rows(3),
                ]))
                ->modalHeading(__('donor.actions.express_interest_heading'))
                ->modalDescription(__('donor.actions.express_interest_description'))
                /** @param array{proposed_amount?: float|null, message?: string|null} $data */
                ->action(function (Initiative $record, array $data): void {
                    /** @var User|null $user */
                    $user = Auth::user();

                    if ($user === null) {
                        return;
                    }

                    $interest = DonorInterest::query()->create([
                        'initiative_id' => $record->id,
                        'user_id' => $user->id,
                        'donor_organization_id' => $user->primary_organization_id,
                        'proposed_amount' => $data['proposed_amount'] ?? null,
                        'message' => $data['message'] ?? null,
                        'status' => 'pending',
                    ]);

                    /** @var Role|null $adminRole */
                    $adminRole = Role::query()->where('name', 'super_admin')->first();
                    if ($adminRole !== null) {
                        $admins = $adminRole->users()->get();
                        if ($admins->isNotEmpty()) {
                            NotificationFacade::send(
                                $admins,
                                new DonorInterestNotification($interest),
                            );
                        }
                    }

                    SafeMailer::send(
                        $record->organization?->email,
                        new DonorInterestMail($interest),
                        'donor_interest',
                    );

                    Notification::make()
                        ->success()
                        ->title(__('donor.actions.interest_recorded'))
                        ->send();
                }),
        ];
    }
}
