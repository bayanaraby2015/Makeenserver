<?php

namespace App\Providers\Filament;

use App\Filament\Association\Pages\Dashboard;
use App\Filament\Association\Widgets\AssociationInitiativeStatusChart;
use App\Filament\Association\Widgets\AssociationStatsOverview;
use App\Filament\Association\Widgets\AssociationWorkQueueWidget;
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

class AssociationPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('association')
            ->path('association')
            ->login()
            ->profile(\App\Filament\Pages\EditProfile::class)
            ->databaseNotifications()
            ->colors([
                'primary' => Color::hex(config('brand.panel_colors.association', '#c95760')),
            ])
            ->brandName(__('panels.association.brand'))
            ->brandLogo(fn (): Htmlable => new HtmlString(view('brand.dual-logo')->render()))
            ->brandLogoHeight('5rem')
            ->favicon(asset(config('brand.logo.favicon', '/brand/favicon.png')))
            ->discoverResources(in: app_path('Filament/Association/Resources'), for: 'App\Filament\Association\Resources')
            ->discoverPages(in: app_path('Filament/Association/Pages'), for: 'App\Filament\Association\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Association/Widgets'), for: 'App\Filament\Association\Widgets')
            ->widgets([
                AssociationStatsOverview::class,
                AssociationInitiativeStatusChart::class,
                AssociationWorkQueueWidget::class,
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
