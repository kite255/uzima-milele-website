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
        /*
        |--------------------------------------------------------------------------
        | Lugha ya Mfumo
        |--------------------------------------------------------------------------
        |
        | Filament na Laravel zitatumia Kiswahili kwa maandishi ambayo yana
        | tafsiri ya lugha ya "sw".
        |
        */

        app()->setLocale('sw');


        return $panel
            ->default()

            /*
            |--------------------------------------------------------------------------
            | Panel
            |--------------------------------------------------------------------------
            */

            ->id('admin')
            ->path('admin')
            ->login(Login::class)


            /*
            |--------------------------------------------------------------------------
            | Muonekano
            |--------------------------------------------------------------------------
            */

            ->darkMode(false)


            /*
            |--------------------------------------------------------------------------
            | Utambulisho wa Uzima Milele
            |--------------------------------------------------------------------------
            */

            ->brandName('Uzima Milele')

            ->brandLogo(
                asset('logo.png')
            )

            ->brandLogoHeight('3rem')


            /*
            |--------------------------------------------------------------------------
            | Rangi za Mfumo
            |--------------------------------------------------------------------------
            */

            ->colors([
                'primary' => Color::hex('#0083CB'),
                'warning' => Color::hex('#F4B122'),
            ])


            /*
            |--------------------------------------------------------------------------
            | Navigation Kulingana na Role
            |--------------------------------------------------------------------------
            |
            | Admin:
            | - Anatumia Dashboard ya kawaida ya Filament.
            | - Anaona Kituo cha Msimamizi kupitia Filament page.
            |
            | Mwalimu:
            | - Anatumia Dashboard ya kawaida.
            | - Anaona Kituo cha Mwalimu.
            |
            */

            ->navigationItems([

                NavigationItem::make('Kituo cha Mwalimu')

                    ->icon(
                        'heroicon-o-academic-cap'
                    )

                    ->url(
                        fn (): string =>
                            route(
                                'instructor.dashboard'
                            )
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
            | Kurasa
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