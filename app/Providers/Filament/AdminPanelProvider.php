<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
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
            ->login()

            /*
            |--------------------------------------------------------------------------
            | Uzima Milele Branding
            |--------------------------------------------------------------------------
            */
            ->brandName('Uzima Milele')
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => [
                    50 => 'eff9ff',
                    100 => 'def2ff',
                    200 => 'b6e8ff',
                    300 => '75d7ff',
                    400 => '2cc3ff',
                    500 => '0083CB',
                    600 => '076994',
                    700 => '0E3D4F',
                    800 => '0E3D4F',
                    900 => '082b38',
                    950 => '041923',
                ],

                'warning' => [
                    50 => 'fff8e6',
                    100 => 'ffefc2',
                    200 => 'ffe08a',
                    300 => 'ffd052',
                    400 => 'F4B122',
                    500 => 'd99100',
                    600 => 'ad7000',
                    700 => '805100',
                    800 => '5c3900',
                    900 => '332000',
                    950 => '1f1300',
                ],
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