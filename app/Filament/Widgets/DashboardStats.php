<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\QuizResult;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        $lessonsQuery = Lesson::query();
        $enrollmentsQuery = LessonEnrollment::query();
        $certificatesQuery = Certificate::query();
        $quizResultsQuery = QuizResult::query();

        if ($user?->role === 'instructor') {
            static::applyInstructorLessonScope(
                $lessonsQuery,
                $user->id
            );

            static::applyInstructorEnrollmentScope(
                $enrollmentsQuery,
                $user->id
            );

            $certificatesQuery->whereHas(
                'lesson',
                fn (Builder $lessonQuery) =>
                    static::applyInstructorLessonScope(
                        $lessonQuery,
                        $user->id
                    )
            );

            $quizResultsQuery->whereHas(
                'quiz',
                function (Builder $quizQuery) use ($user) {
                    $quizQuery->where(
                        function (Builder $query) use ($user) {
                            $query
                                ->whereHas(
                                    'lesson',
                                    fn (Builder $lessonQuery) =>
                                        static::applyInstructorLessonScope(
                                            $lessonQuery,
                                            $user->id
                                        )
                                )
                                ->orWhereHas(
                                    'module.lesson',
                                    fn (Builder $lessonQuery) =>
                                        static::applyInstructorLessonScope(
                                            $lessonQuery,
                                            $user->id
                                        )
                                )
                                ->orWhereHas(
                                    'topic.module.lesson',
                                    fn (Builder $lessonQuery) =>
                                        static::applyInstructorLessonScope(
                                            $lessonQuery,
                                            $user->id
                                        )
                                );
                        }
                    );
                }
            );
        }

        $totalLessons = $lessonsQuery->count();
        $totalEnrollments = $enrollmentsQuery->count();
        $totalCertificates = $certificatesQuery->count();
        $totalQuizResults = $quizResultsQuery->count();

        $totalStudents = $user?->role === 'admin'
            ? User::query()
                ->where('role', 'student')
                ->count()
            : (clone $enrollmentsQuery)
                ->distinct()
                ->count('user_id');

        return [
            Stat::make(
                'Lessons',
                $totalLessons
            )
                ->description(
                    $user?->role === 'instructor'
                        ? 'Assigned lessons'
                        : 'Total lessons'
                )
                ->descriptionIcon(
                    'heroicon-m-academic-cap'
                )
                ->color('primary'),

            Stat::make(
                'Students',
                $totalStudents
            )
                ->description(
                    $user?->role === 'instructor'
                        ? 'Your enrolled students'
                        : 'Registered students'
                )
                ->descriptionIcon(
                    'heroicon-m-users'
                )
                ->color('success'),

            Stat::make(
                'Enrollments',
                $totalEnrollments
            )
                ->description(
                    'Lesson enrollments'
                )
                ->descriptionIcon(
                    'heroicon-m-book-open'
                )
                ->color('warning'),

            Stat::make(
                'Certificates',
                $totalCertificates
            )
                ->description(
                    'Issued certificates'
                )
                ->descriptionIcon(
                    'heroicon-m-document-check'
                )
                ->color('success'),

            Stat::make(
                'Quiz Results',
                $totalQuizResults
            )
                ->description(
                    'Submitted quiz attempts'
                )
                ->descriptionIcon(
                    'heroicon-m-question-mark-circle'
                )
                ->color('info'),
        ];
    }

    protected static function applyInstructorLessonScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $lessonQuery) use ($instructorId) {
                $lessonQuery
                    ->where(
                        'lead_instructor_id',
                        $instructorId
                    )
                    ->orWhereHas(
                        'followUpInstructors',
                        fn (Builder $followUpQuery) =>
                            $followUpQuery->where(
                                'users.id',
                                $instructorId
                            )
                    )
                    ->orWhere(
                        function (Builder $legacyQuery) use ($instructorId) {
                            $legacyQuery
                                ->where(
                                    'instructor_id',
                                    $instructorId
                                )
                                ->whereNull(
                                    'lead_instructor_id'
                                )
                                ->whereDoesntHave(
                                    'followUpInstructors'
                                );
                        }
                    );
            }
        );
    }

    protected static function applyInstructorEnrollmentScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $enrollmentQuery) use ($instructorId) {
                $enrollmentQuery
                    ->where(
                        'follow_up_instructor_id',
                        $instructorId
                    )
                    ->orWhereHas(
                        'lesson',
                        fn (Builder $lessonQuery) =>
                            $lessonQuery->where(
                                'lead_instructor_id',
                                $instructorId
                            )
                    )
                    ->orWhereHas(
                        'lesson',
                        function (Builder $lessonQuery) use ($instructorId) {
                            $lessonQuery
                                ->where(
                                    'instructor_id',
                                    $instructorId
                                )
                                ->whereNull(
                                    'lead_instructor_id'
                                )
                                ->whereDoesntHave(
                                    'followUpInstructors'
                                );
                        }
                    );
            }
        );
    }
}
