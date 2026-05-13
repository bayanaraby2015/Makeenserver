<?php

namespace App\Filament\Resources\ZoomSettings\Pages;

use App\Filament\Resources\ZoomSettings\ZoomSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateZoomSetting extends CreateRecord
{
    protected static string $resource = ZoomSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return ZoomSettingResource::getUrl('index');
    }
}
