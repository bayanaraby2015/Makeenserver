<?php

namespace App\Filament\Resources\ZoomSettings\Pages;

use App\Filament\Resources\ZoomSettings\ZoomSettingResource;
use App\Models\ZoomSetting;
use App\Support\ZoomMeetingScheduler;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListZoomSettings extends ListRecords
{
    protected static string $resource = ZoomSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => ZoomSetting::query()->doesntExist()),

            Action::make('test_connection')
                ->label(__('zoom_settings.actions.test_connection'))
                ->icon(Heroicon::OutlinedSignal)
                ->color('info')
                ->visible(fn (): bool => ZoomSetting::query()->exists())
                ->action(function (): void {
                    $result = app(ZoomMeetingScheduler::class)->testConnection();

                    if ($result['ok']) {
                        $detail = __('zoom_settings.test.success_detail', [
                            'name' => $result['details']['display_name'] ?? '-',
                            'email' => $result['details']['email'] ?? '-',
                        ]);

                        Notification::make()
                            ->success()
                            ->title($result['message'])
                            ->body($detail)
                            ->persistent()
                            ->send();

                        return;
                    }

                    $body = $result['message'];
                    if (! empty($result['details'])) {
                        $body .= "\n".__('zoom_settings.test.error_detail', [
                            'status' => $result['details']['status'] ?? '-',
                            'body' => is_string($result['details']['body'] ?? null)
                                ? $result['details']['body']
                                : json_encode($result['details']['body'] ?? '-', JSON_UNESCAPED_UNICODE),
                        ]);
                    }

                    Notification::make()
                        ->danger()
                        ->title($result['message'])
                        ->body($body)
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
