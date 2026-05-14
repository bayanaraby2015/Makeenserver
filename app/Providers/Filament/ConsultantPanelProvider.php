<?php

namespace App\Providers\Filament;

use App\Filament\Consultant\Pages\Dashboard;
use App\Filament\Consultant\Widgets\ConsultantInitiativeStatusChart;
use App\Filament\Consultant\Widgets\ConsultantStatsOverview;
use App\Filament\Consultant\Widgets\ConsultantWorkQueueWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ConsultantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('consultant')
            ->path('consultant')
            ->login()
            ->profile(\App\Filament\Pages\EditProfile::class)
            ->databaseNotifications()
            ->colors([
                'primary' => Color::hex(config('brand.panel_colors.consultant', '#2b354f')),
            ])
            ->brandName(__('panels.consultant.brand'))
            ->brandLogo(fn (): Htmlable => new HtmlString(view('brand.dual-logo')->render()))
            ->brandLogoHeight('5rem')
            ->favicon(asset(config('brand.logo.favicon', '/brand/favicon.png')))
            ->discoverResources(in: app_path('Filament/Consultant/Resources'), for: 'App\Filament\Consultant\Resources')
            ->discoverPages(in: app_path('Filament/Consultant/Pages'), for: 'App\Filament\Consultant\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Consultant/Widgets'), for: 'App\Filament\Consultant\Widgets')
            ->widgets([
                ConsultantStatsOverview::class,
                ConsultantInitiativeStatusChart::class,
                ConsultantWorkQueueWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
