<?php

namespace App\Filament\Pages;

use App\Http\Controllers\InstructorDashboardController;
use Filament\Pages\Page;

class InstructorHub extends Page
{
    protected static ?string $navigationIcon =
        'heroicon-o-academic-cap';

    protected static ?string $navigationLabel =
        'Instructor Hub';

    protected static ?string $title =
        'Instructor Hub';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = null;

    protected static string $view =
        'filament.pages.instructor-hub';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'instructor';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'instructor';
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        abort_unless(
            $user && $user->role === 'instructor',
            403
        );

        return app(
            InstructorDashboardController::class
        )->getDashboardData($user);
    }
}