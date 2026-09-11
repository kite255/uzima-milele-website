<?php

namespace App\Filament\Pages;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use Filament\Pages\Page;

class AdminCenter extends Page
{
    protected static ?string $navigationIcon =
        'heroicon-o-shield-check';

    protected static ?string $navigationLabel =
        'Admin Center';

    protected static ?string $title =
        'Admin Center';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = null;

    protected static string $view =
        'filament.pages.admin-center';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    protected function getViewData(): array
    {
        return [
            'totalUsers' => User::query()->count(),

            'totalStudents' => User::query()
                ->where('role', 'student')
                ->count(),

            'totalInstructors' => User::query()
                ->where('role', 'instructor')
                ->count(),

            'totalLessons' => Lesson::query()->count(),

            'totalEnrollments' =>
                LessonEnrollment::query()->count(),

            'pendingQuestions' =>
                LessonQuestion::query()
                    ->whereNull('answer')
                    ->count(),
        ];
    }
}