<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\QuizResult;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_if(! in_array($user->role, ['admin', 'instructor']), 403);

        /*
        |--------------------------------------------------------------------------
        | Admin sees all lessons, instructor sees only assigned lessons
        |--------------------------------------------------------------------------
        */
        $lessonQuery = Lesson::query();

        if ($user->role === 'instructor') {
            $lessonQuery->where('instructor_id', $user->id);
        }

        $lessonIds = (clone $lessonQuery)->pluck('id');

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

        $totalStudents = LessonEnrollment::whereIn('lesson_id', $lessonIds)
            ->distinct('user_id')
            ->count('user_id');

        $pendingQuestions = LessonQuestion::whereIn('lesson_id', $lessonIds)
            ->whereNull('answer')
            ->count();

        $answeredQuestions = LessonQuestion::whereIn('lesson_id', $lessonIds)
            ->whereNotNull('answer')
            ->count();

        $certificatesIssued = Certificate::whereIn('lesson_id', $lessonIds)
            ->count();

        $recentQuestions = LessonQuestion::with(['lesson', 'user'])
            ->whereIn('lesson_id', $lessonIds)
            ->latest()
            ->take(8)
            ->get();

        $recentQuizResults = QuizResult::with(['quiz'])
            ->latest()
            ->take(8)
            ->get();

        return view('instructor.dashboard', compact(
            'lessons',
            'totalLessons',
            'totalStudents',
            'pendingQuestions',
            'answeredQuestions',
            'certificatesIssued',
            'recentQuestions',
            'recentQuizResults'
        ));
    }
}