<?php

namespace App\Filament\Resources\Initiatives\Pages;

use App\Filament\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use App\Models\Organization;
use App\Support\InitiativeSpecializations;
use App\Support\InitiativeWordImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListInitiatives extends ListRecords
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_word')
                ->label(__('initiatives.actions.import_word'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->modalHeading(__('initiatives.import.modal_heading'))
                ->modalDescription(__('initiatives.import.modal_description'))
                ->form([
                    FileUpload::make('document')
                        ->label(__('initiatives.import.document'))
                        ->disk('local')
                        ->directory('initiative-imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->required(),

                    Select::make('organization_id')
                        ->label(__('initiatives.fields.organization'))
                        ->options(fn (): array => Organization::query()
                            ->where('type', 'association')
                            ->where('status', 'active')
                            ->orderBy('name_ar')
                            ->pluck('name_ar', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Select::make('specializations')
                        ->label(__('initiatives.fields.specializations'))
                        ->multiple()
                        ->options(InitiativeSpecializations::options())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $document = $data['document'] ?? null;
                    $document = is_array($document) ? reset($document) : $document;

                    if (! is_string($document) || $document === '') {
                        Notification::make()
                            ->danger()
                            ->title(__('initiatives.import.failed'))
                            ->body(__('initiatives.import.invalid_document'))
                            ->send();

                        return;
                    }

                    $specializations = array_values((array) ($data['specializations'] ?? []));

                    try {
                        $initiative = app(InitiativeWordImporter::class)->import(
                            Storage::disk('local')->path($document),
                            [
                                'organization_id' => (int) $data['organization_id'],
                                'domain' => $specializations[0] ?? null,
                                'specializations' => $specializations,
                            ],
                        );
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->danger()
                            ->title(__('initiatives.import.failed'))
                            ->body($exception->getMessage())
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title(__('initiatives.import.success'))
                        ->body(__('initiatives.import.success_body'))
                        ->send();

                    $this->redirect(InitiativeResource::getUrl('edit', ['record' => $initiative]));
                }),

            Action::make('export_csv')
                ->label(__('initiatives.actions.export_csv'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->downloadInitiativesCsv()),

            CreateAction::make(),
        ];
    }

    protected function downloadInitiativesCsv(): StreamedResponse
    {
        $filename = 'initiatives-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                return;
            }

            // BOM for Excel UTF-8 compatibility
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                __('initiatives.fields.name_ar'),
                __('initiatives.fields.organization'),
                __('initiatives.fields.domain'),
                __('initiatives.fields.status'),
                __('initiatives.fields.grand_total'),
                __('initiatives.fields.submitted_at'),
                __('initiatives.fields.approved_at'),
            ]);

            Initiative::query()
                ->with('organization')
                ->orderByDesc('created_at')
                ->chunk(200, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            $row->name_ar,
                            $row->organization->name_ar ?? '',
                            __('initiatives.domains.'.$row->domain),
                            __('initiatives.statuses.'.$row->status),
                            $row->grand_total,
                            $row->submitted_at?->format('Y-m-d H:i'),
                            $row->approved_at?->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
