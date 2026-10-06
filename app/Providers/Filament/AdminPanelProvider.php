<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\OverdueStudents;
use App\Filament\Resources\LessonEnrollmentResource;
use App\Filament\Resources\LessonQuestionResource;
use App\Filament\Resources\LessonResource;
use App\Filament\Resources\QuizResultResource;
use App\Filament\Widgets\DashboardStats;
use App\Models\Lesson;
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

                NavigationItem::make('My Lessons')
                    ->group('Teaching')
                    ->icon('heroicon-o-book-open')
                    ->url(
                        fn (): string =>
                            LessonResource::getUrl('index')
                    )
                    ->sort(10)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Student Join Links')
                    ->group('Teaching')
                    ->icon('heroicon-o-link')
                    ->url(
                        fn (): string =>
                            route('instructor.enrollment-links.index')
                    )
                    ->sort(20)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Student Q&A')
                    ->group('Teaching')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(
                        fn (): string =>
                            LessonQuestionResource::getUrl('index')
                    )
                    ->sort(30)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Assigned Students')
                    ->group('Students')
                    ->icon('heroicon-o-user-group')
                    ->url(
                        fn (): string =>
                            LessonEnrollmentResource::getUrl('index')
                    )
                    ->sort(40)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Due Follow-ups')
                    ->group('Students')
                    ->icon('heroicon-o-clock')
                    ->url(
                        fn (): string =>
                            route('instructor.dashboard') . '#due-follow-ups'
                    )
                    ->sort(50)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Overdue Students')
                    ->group('Students')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->url(
                        fn (): string =>
                            OverdueStudents::getUrl()
                    )
                    ->sort(60)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Quiz Results')
                    ->group('Students')
                    ->icon('heroicon-o-document-chart-bar')
                    ->url(
                        fn (): string =>
                            QuizResultResource::getUrl('index')
                    )
                    ->sort(70)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Team Supervision')
                    ->group('Students')
                    ->icon('heroicon-o-user-group')
                    ->url(
                        fn (): string =>
                            route('instructor.dashboard') . '#team-supervision'
                    )
                    ->sort(75)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                            && Lesson::query()
                                ->where(
                                    'lead_instructor_id',
                                    auth()->id()
                                )
                                ->exists()
                    ),

                NavigationItem::make('Notifications')
                    ->group('Account')
                    ->icon('heroicon-o-bell')
                    ->url(
                        fn (): string =>
                            route('notifications.index')
                    )
                    ->sort(80)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'instructor'
                    ),

                NavigationItem::make('Profile')
                    ->group('Account')
                    ->icon('heroicon-o-user-circle')
                    ->url(
                        fn (): string =>
                            route('profile.edit')
                    )
                    ->sort(90)
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
            | Database Notifications
            |--------------------------------------------------------------------------
            */
            ->databaseNotifications()

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
