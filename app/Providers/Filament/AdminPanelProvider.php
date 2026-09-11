<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\DashboardStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)

            /*
            |--------------------------------------------------------------------------
            | Global Appearance
            |--------------------------------------------------------------------------
            |
            | Keep the admin panel in light mode to avoid white text being applied
            | to light custom backgrounds.
            |
            */
            ->darkMode(false)

            /*
            |--------------------------------------------------------------------------
            | Uzima Milele Branding
            |--------------------------------------------------------------------------
            */
            ->brandName('Uzima Milele')
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('3rem')

            /*
            |--------------------------------------------------------------------------
            | Filament Colours
            |--------------------------------------------------------------------------
            |
            | Let Filament generate valid colour shades instead of manually passing
            | hexadecimal strings as shade values.
            |
            */
            ->colors([
                'primary' => Color::hex('#0083CB'),
                'warning' => Color::hex('#F4B122'),
            ])

            /*
            |--------------------------------------------------------------------------
            | Resources / Pages / Widgets
            |--------------------------------------------------------------------------
            */
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                DashboardStats::class,
            ])

            /*
            |--------------------------------------------------------------------------
            | Middleware
            |--------------------------------------------------------------------------
            */
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