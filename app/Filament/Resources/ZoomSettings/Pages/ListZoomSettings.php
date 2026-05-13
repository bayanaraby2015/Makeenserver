<?php

namespace App\Filament\Resources\ZoomSettings\Pages;

use App\Filament\Resources\ZoomSettings\ZoomSettingResource;
use App\Models\ZoomSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZoomSettings extends ListRecords
{
    protected static string $resource = ZoomSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => ZoomSetting::query()->doesntExist()),
        ];
    }
}
