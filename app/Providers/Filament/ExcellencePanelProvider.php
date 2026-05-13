<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Excellence\Pages\Dashboard;
use App\Filament\Excellence\Widgets\ExcellenceActivityWidget;
use App\Filament\Excellence\Widgets\ExcellenceInitiativeStatusChart;
use App\Filament\Excellence\Widgets\ExcellenceInitiativesWidget;
use App\Filament\Excellence\Widgets\ExcellenceStatsOverview;
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

class ExcellencePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('excellence')
            ->path('excellence')
            ->login()
            ->profile()
            ->databaseNotifications()
            ->colors([
                'primary' => Color::hex(config('brand.panel_colors.excellence', '#283979')),
            ])
            ->brandName(__('panels.excellence.brand'))
            ->brandLogo(fn (): Htmlable => new HtmlString(view('brand.dual-logo')->render()))
            ->brandLogoHeight('5rem')
            ->favicon(asset(config('brand.logo.favicon', '/brand/favicon.png')))
            ->discoverResources(in: app_path('Filament/Excellence/Resources'), for: 'App\Filament\Excellence\Resources')
            ->discoverPages(in: app_path('Filament/Excellence/Pages'), for: 'App\Filament\Excellence\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Excellence/Widgets'), for: 'App\Filament\Excellence\Widgets')
            ->widgets([
                ExcellenceStatsOverview::class,
                ExcellenceInitiativeStatusChart::class,
                ExcellenceInitiativesWidget::class,
                ExcellenceActivityWidget::class,
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
