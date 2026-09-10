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
        | Student Visibility
        |--------------------------------------------------------------------------
        |
        | Lead instructor:
        | - Sees every student enrolled in lessons they lead.
        |
        | Follow-up instructor:
        | - Sees only students assigned directly to them.
        |
        | Legacy instructor:
        | - Sees students from old instructor_id lessons only when those
        |   lessons have not been configured with the new structure.
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

        /*
        |--------------------------------------------------------------------------
        | Student Count
        |--------------------------------------------------------------------------
        */
        $totalStudents = (clone $studentQuery)
            ->distinct()
            ->count('user_id');

        /*
        |--------------------------------------------------------------------------
        | Students Visible on Dashboard
        |--------------------------------------------------------------------------
        */
        $assignedStudents = (clone $studentQuery)
            ->with([
                'user',
                'lesson',
                'followUpInstructor',
            ])
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Due Follow-ups
        |--------------------------------------------------------------------------
        */
        $dueFollowUps = (clone $studentQuery)
            ->with([
                'user',
                'lesson',
                'followUpInstructor',
            ])
            ->whereNotNull(
                'next_follow_up_at'
            )
            ->where(
                'next_follow_up_at',
                '<=',
                now()
            )
            ->orderBy(
                'next_follow_up_at'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Lead Instructor Team Supervision
        |--------------------------------------------------------------------------
        |
        | Only instructors who are lead instructors receive supervision data.
        |
        | Follow-up instructors:
        | - Cannot see the supervision section.
        | - Cannot see another follow-up instructor's workload.
        |
        | Lead instructors:
        | - See follow-up instructors attached to lessons they lead.
        | - See student counts for each instructor.
        | - See due follow-up counts for each instructor.
        | - See students who have not yet been assigned.
        |
        */
        $canViewTeamSupervision = false;

        $teamSupervision = collect();

        $unassignedStudents = collect();

        if ($user->role === 'instructor') {
            $ledLessons = Lesson::query()
                ->with([
                    'followUpInstructors',
                ])
                ->where(
                    'lead_instructor_id',
                    $user->id
                )
                ->orderBy('title')
                ->get();

            $canViewTeamSupervision = $ledLessons->isNotEmpty();

            if ($canViewTeamSupervision) {
                $ledLessonIds = $ledLessons
                    ->pluck('id');

                /*
                |--------------------------------------------------------------------------
                | Follow-up Instructor Workload
                |--------------------------------------------------------------------------
                */
                $teamSupervision = $ledLessons
                    ->flatMap(
                        function (Lesson $lesson) {
                            return $lesson->followUpInstructors
                                ->map(
                                    function ($instructor) use ($lesson) {
                                        $studentCount = LessonEnrollment::query()
                                            ->where(
                                                'lesson_id',
                                                $lesson->id
                                            )
                                            ->where(
                                                'follow_up_instructor_id',
                                                $instructor->id
                                            )
                                            ->count();

                                        $dueFollowUpCount = LessonEnrollment::query()
                                            ->where(
                                                'lesson_id',
                                                $lesson->id
                                            )
                                            ->where(
                                                'follow_up_instructor_id',
                                                $instructor->id
                                            )
                                            ->whereNotNull(
                                                'next_follow_up_at'
                                            )
                                            ->where(
                                                'next_follow_up_at',
                                                '<=',
                                                now()
                                            )
                                            ->count();

                                        return [
                                            'lesson' => $lesson,
                                            'instructor' => $instructor,
                                            'student_count' => $studentCount,
                                            'due_follow_up_count' => $dueFollowUpCount,
                                        ];
                                    }
                                );
                        }
                    )
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Unassigned Students
                |--------------------------------------------------------------------------
                */
                $unassignedStudents = LessonEnrollment::query()
                    ->with([
                        'user',
                        'lesson',
                    ])
                    ->whereIn(
                        'lesson_id',
                        $ledLessonIds
                    )
                    ->whereNull(
                        'follow_up_instructor_id'
                    )
                    ->latest('id')
                    ->get();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | Certificates
        |--------------------------------------------------------------------------
        */
        $certificatesIssued = Certificate::query()
            ->whereIn(
                'lesson_id',
                $lessonIds
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Questions
        |--------------------------------------------------------------------------
        */
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
        */
        $recentQuizResults = QuizResult::query()
            ->with([
                'quiz',
            ])
            ->latest()
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        return view(
            'instructor.dashboard',
            compact(
                'lessons',
                'totalLessons',
                'totalStudents',
                'assignedStudents',
                'dueFollowUps',
                'canViewTeamSupervision',
                'teamSupervision',
                'unassignedStudents',
                'pendingQuestions',
                'answeredQuestions',
                'certificatesIssued',
                'recentQuestions',
                'recentQuizResults'
            )
        );
    }
}