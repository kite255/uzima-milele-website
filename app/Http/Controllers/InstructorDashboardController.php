<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\QuizResult;
use Illuminate\Database\Eloquent\Builder;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_if(
            ! $user
            || ! in_array(
                $user->role,
                ['admin', 'instructor'],
                true
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Lessons Visible on the Instructor Dashboard
        |--------------------------------------------------------------------------
        |
        | Admin:
        | - Sees every lesson.
        |
        | Lead instructor:
        | - Sees every lesson they lead.
        |
        | Follow-up instructor:
        | - Sees lessons where they belong to the follow-up team.
        |
        | Legacy compatibility:
        | - Old lessons using instructor_id remain visible until they are
        |   configured with the new lead/follow-up structure.
        |
        */
        $lessonQuery = Lesson::query();

        if ($user->role === 'instructor') {
            $lessonQuery->where(
                function (Builder $query) use ($user) {
                    $query
                        ->where(
                            'lead_instructor_id',
                            $user->id
                        )
                        ->orWhereHas(
                            'followUpInstructors',
                            fn (Builder $followUpQuery) =>
                                $followUpQuery->where(
                                    'users.id',
                                    $user->id
                                )
                        )
                        ->orWhere(
                            function (Builder $legacyQuery) use ($user) {
                                $legacyQuery
                                    ->where(
                                        'instructor_id',
                                        $user->id
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

        $lessonIds = (clone $lessonQuery)
            ->pluck('id');

        $lessons = (clone $lessonQuery)
            ->withCount([
                'modules',
                'topics',
                'enrollments',
                'questions',
            ])
            ->latest()
            ->get();

        $totalLessons = $lessons->count();

        /*
        |--------------------------------------------------------------------------
        | Student Visibility / Counts
        |--------------------------------------------------------------------------
        |
        | Lead instructor:
        | - Counts every student in courses they lead.
        |
        | Follow-up instructor:
        | - Counts only enrollments assigned directly to them.
        |
        | Legacy instructor:
        | - Counts students from old instructor_id lessons only when that
        |   lesson has not yet been configured with the new structure.
        |
        */
        $studentQuery = LessonEnrollment::query();

        if ($user->role === 'instructor') {
            $studentQuery->where(
                function (Builder $query) use ($user) {
                    $query
                        ->where(
                            'follow_up_instructor_id',
                            $user->id
                        )
                        ->orWhereHas(
                            'lesson',
                            fn (Builder $lessonQuery) =>
                                $lessonQuery->where(
                                    'lead_instructor_id',
                                    $user->id
                                )
                        )
                        ->orWhereHas(
                            'lesson',
                            function (Builder $lessonQuery) use ($user) {
                                $lessonQuery
                                    ->where(
                                        'instructor_id',
                                        $user->id
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
        } else {
            $studentQuery->whereIn(
                'lesson_id',
                $lessonIds
            );
        }

        $totalStudents = $studentQuery
            ->distinct()
            ->count('user_id');

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        |
        | These remain lesson-scoped for now. Instructor-specific question
        | ownership is handled separately by the question-management layer.
        |
        */
        $pendingQuestions = LessonQuestion::query()
            ->whereIn(
                'lesson_id',
                $lessonIds
            )
            ->whereNull('answer')
            ->count();

        $answeredQuestions = LessonQuestion::query()
            ->whereIn(
                'lesson_id',
                $lessonIds
            )
            ->whereNotNull('answer')
            ->count();

        $certificatesIssued = Certificate::query()
            ->whereIn(
                'lesson_id',
                $lessonIds
            )
            ->count();

        $recentQuestions = LessonQuestion::query()
            ->with([
                'lesson',
                'user',
            ])
            ->whereIn(
                'lesson_id',
                $lessonIds
            )
            ->latest()
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Quiz Results
        |--------------------------------------------------------------------------
        |
        | Keep the existing behavior here for this task. Quiz-result access is
        | updated separately together with QuizResultResource so that its
        | lesson/module/topic relationship rules stay consistent.
        |
        */
        $recentQuizResults = QuizResult::query()
            ->with(['quiz'])
            ->latest()
            ->take(8)
            ->get();

        return view(
            'instructor.dashboard',
            compact(
                'lessons',
                'totalLessons',
                'totalStudents',
                'pendingQuestions',
                'answeredQuestions',
                'certificatesIssued',
                'recentQuestions',
                'recentQuizResults'
            )
        );
    }
}
