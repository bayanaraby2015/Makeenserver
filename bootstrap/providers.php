<?php

use App\Providers\AppServiceProvider;
use App\Providers\BrandServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AssociationPanelProvider;
use App\Providers\Filament\ConsultantPanelProvider;
use App\Providers\Filament\DonorPanelProvider;
use App\Providers\Filament\ExcellencePanelProvider;

return [
    AppServiceProvider::class,
    BrandServiceProvider::class,
    AdminPanelProvider::class,
    AssociationPanelProvider::class,
    ConsultantPanelProvider::class,
    DonorPanelProvider::class,
    ExcellencePanelProvider::class,
];
