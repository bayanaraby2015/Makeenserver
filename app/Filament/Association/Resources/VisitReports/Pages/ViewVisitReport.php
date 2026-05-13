<?php

namespace App\Filament\Association\Resources\VisitReports\Pages;

use App\Filament\Association\Resources\VisitReports\VisitReportResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\VisitReport;
use App\Notifications\VisitAppointmentSelectedNotification;
use App\Support\VisitAppointmentFormatter;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ViewVisitReport extends ViewRecord
{
    protected static string $resource = VisitReportResource::class;

    protected string $view = 'filament.visit-reports.association-view';

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('visit_report', fn (VisitReport $record): ?int => $record->organization_id),
            Action::make('choose_appointment')
                ->label('اختيار موعد الزيارة')
                ->color('primary')
                ->visible(fn (VisitReport $record): bool => $record->status === 'proposed' && ! empty($record->appointment_options))
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Radio::make('starts_at')
                        ->label('اختر الموعد المناسب')
                        ->options(fn (): array => collect($this->getRecord()->appointment_options ?? [])
                            ->filter(fn (mixed $option): bool => is_array($option) && filled($option['starts_at'] ?? null))
                            ->mapWithKeys(fn (array $option): array => [
                                $option['starts_at'] => VisitAppointmentFormatter::dateTime($option['starts_at']),
                            ])
                            ->all())
                        ->required(),
                ]))
                /** @param array{starts_at: string} $data */
                ->action(function (VisitReport $record, array $data): void {
                    $record->update([
                        'scheduled_at' => VisitAppointmentFormatter::storeValue($data['starts_at']),
                        'selected_at' => now(),
                        'selected_by' => Auth::id(),
                        'status' => 'planned',
                    ]);

                    if ($record->consultant) {
                        $record->consultant->notify(new VisitAppointmentSelectedNotification($record->fresh(['initiative', 'consultant'])));
                    }

                    Notification::make()
                        ->success()
                        ->title('تم اعتماد موعد الزيارة وإبلاغ المستشار')
                        ->send();
                }),
        ];
    }
}
