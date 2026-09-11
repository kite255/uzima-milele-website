<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\DashboardStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
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
            */
            ->colors([
                'primary' => Color::hex('#0083CB'),
                'warning' => Color::hex('#F4B122'),
            ])

            /*
            |--------------------------------------------------------------------------
            | Role-Based Navigation
            |--------------------------------------------------------------------------
            |
            | Admin:
            | - Uses the standard Dashboard.
            | - Sees Admin Center through its Filament page registration.
            |
            | Instructor:
            | - Uses the standard Dashboard.
            | - Sees Instructor Hub here.
            |
            | Instructor Hub reuses the existing instructor dashboard instead
            | of creating a second copy of its business logic.
            |
            */
            ->navigationItems([
                NavigationItem::make('Instructor Hub')
                    ->icon('heroicon-o-academic-cap')
                    ->url(
                        fn (): string =>
                            route('instructor.dashboard')
                    )
                    ->sort(1)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Resources
            |--------------------------------------------------------------------------
            */
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])

            /*
            |--------------------------------------------------------------------------
            | Widgets
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | Authentication Middleware
            |--------------------------------------------------------------------------
            */
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}