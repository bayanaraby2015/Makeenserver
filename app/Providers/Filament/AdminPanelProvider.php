<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminConsultationStatusChart;
use App\Filament\Widgets\AdminInitiativeStatusChart;
use App\Filament\Widgets\AdminOperationsDashboardWidget;
use App\Filament\Widgets\InitiativesStatsOverview;
use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->databaseNotifications()
            ->colors([
                'primary' => Color::hex(config('brand.panel_colors.admin', '#283979')),
            ])
            ->brandName(__('panels.admin.brand'))
            ->brandLogo(fn (): Htmlable => new HtmlString(view('brand.dual-logo')->render()))
            ->brandLogoHeight('5rem')
            ->favicon(asset(config('brand.logo.favicon', '/brand/favicon.png')))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AdminOperationsDashboardWidget::class,
                StatsOverview::class,
                InitiativesStatsOverview::class,
                AdminInitiativeStatusChart::class,
                AdminConsultationStatusChart::class,
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
