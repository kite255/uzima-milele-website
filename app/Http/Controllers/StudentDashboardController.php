<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\LessonProgress;
use App\Models\QuizResult;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Certificates
        |--------------------------------------------------------------------------
        */
        $certificates = Certificate::query()
            ->with('lesson')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->keyBy('lesson_id');

        /*
        |--------------------------------------------------------------------------
        | Enrolled Lessons
        |--------------------------------------------------------------------------
        */
        $lessons = $user->enrolledLessons()
            ->withPivot([
                'enrolled_at',
                'study_pace',
                'study_hours_per_week',
                'target_completion_date',
                'schedule_started_at',
                'schedule_updated_at',
            ])
            ->with([
                'instructor',

                'modules' => fn ($q) => $q
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics' => fn ($q) => $q
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics.quiz' => fn ($q) => $q
                    ->where('is_published', true),

                'modules.topics.quiz.questions' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order'),

                'modules.quizzes' => fn ($q) => $q
                    ->where('is_published', true),

                'modules.quizzes.questions' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order'),

                'finalQuiz' => fn ($q) => $q
                    ->where('is_published', true),

                'finalQuiz.questions' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->where('lessons.is_published', true)
            ->orderByPivot('enrolled_at', 'desc')
            ->get()
            ->map(function ($lesson) use ($user, $certificates) {

                /*
                |--------------------------------------------------------------------------
                | Topics and Progress
                |--------------------------------------------------------------------------
                */
                $allTopics = $lesson->modules
                    ->flatMap(fn ($module) => $module->topics)
                    ->values();

                $topicIds = $allTopics->pluck('id');

                $totalTopics = $allTopics->count();

                $completedTopicIds = LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->whereIn('lesson_topic_id', $topicIds)
                    ->pluck('lesson_topic_id')
                    ->unique()
                    ->values()
                    ->toArray();

                $completedTopics = count($completedTopicIds);

                $progress = $totalTopics > 0
                    ? (int) round(($completedTopics / $totalTopics) * 100)
                    : 0;

                $progress = min(100, max(0, $progress));

                $lesson->completed_topics_count = $completedTopics;
                $lesson->total_topics_count = $totalTopics;
                $lesson->progress = $progress;

                /*
                |--------------------------------------------------------------------------
                | Next Topic
                |--------------------------------------------------------------------------
                */
                $lesson->next_topic = $allTopics
                    ->first(fn ($topic) => ! in_array($topic->id, $completedTopicIds, true));

                /*
                |--------------------------------------------------------------------------
                | Completion Logic
                |--------------------------------------------------------------------------
                */
                $topicsCompleted = $totalTopics > 0 && $completedTopics >= $totalTopics;

                /*
                |--------------------------------------------------------------------------
                | Final Quiz Logic
                |--------------------------------------------------------------------------
                */
                $finalQuiz = $lesson->finalQuiz;

                $finalQuizRequired = (bool) ($finalQuiz?->is_required);

                $finalQuizPassed = true;

                if ($finalQuiz && $finalQuizRequired) {
                    $finalQuizPassed = QuizResult::query()
                        ->where('user_id', $user->id)
                        ->where('quiz_id', $finalQuiz->id)
                        ->where('passed', true)
                        ->exists();
                }

                /*
                |--------------------------------------------------------------------------
                | Certificate Logic
                |--------------------------------------------------------------------------
                */
                $certificate = $certificates->get($lesson->id);

                $lesson->certificate = $certificate;
                $lesson->final_quiz = $finalQuiz;
                $lesson->final_quiz_required = $finalQuizRequired;
                $lesson->final_quiz_passed = $finalQuizPassed;

                $lesson->can_generate_certificate = $topicsCompleted
                    && $finalQuizPassed
                    && ! $certificate;

                $lesson->is_completed = $topicsCompleted && $finalQuizPassed;

                return $lesson;
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Stats
        |--------------------------------------------------------------------------
        */
        $totalLessons = $lessons->count();

        $completedLessons = $lessons
            ->filter(fn ($lesson) => $lesson->is_completed)
            ->count();

        $overallProgress = $totalLessons > 0
            ? (int) round($lessons->avg('progress'))
            : 0;

        $overallProgress = min(100, max(0, $overallProgress));

        /*
        |--------------------------------------------------------------------------
        | Quiz Results
        |--------------------------------------------------------------------------
        */
        $quizResults = QuizResult::query()
            ->with('quiz')
            ->where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->latest()
            ->take(10)
            ->get();

        $quizAttempts = QuizResult::query()
            ->where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->count();

        $passedQuizzes = QuizResult::query()
            ->where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->where('passed', true)
            ->count();

        return view('student.dashboard', compact(
            'lessons',
            'totalLessons',
            'completedLessons',
            'overallProgress',
            'quizResults',
            'quizAttempts',
            'passedQuizzes',
            'certificates'
        ));
    }
}