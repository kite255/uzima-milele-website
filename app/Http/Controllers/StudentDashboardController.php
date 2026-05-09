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

        $certificates = Certificate::with('lesson')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->keyBy('lesson_id');

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
                'modules' => fn ($q) => $q
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics' => fn ($q) => $q
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics.quiz.questions',

                'finalQuiz' => fn ($q) => $q
                    ->where('is_published', true),

                'finalQuiz.questions',
            ])
            ->where('lessons.is_published', true)
            ->orderByPivot('enrolled_at', 'desc')
            ->get()
            ->map(function ($lesson) use ($user, $certificates) {
                $allTopics = $lesson->modules
                    ->flatMap(fn ($module) => $module->topics)
                    ->values();

                $topicIds = $allTopics->pluck('id');

                $totalTopics = $allTopics->count();

                $completedTopicIds = LessonProgress::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->whereIn('lesson_topic_id', $topicIds)
                    ->pluck('lesson_topic_id')
                    ->unique()
                    ->values()
                    ->toArray();

                $completedTopics = count($completedTopicIds);

                $lesson->completed_topics_count = $completedTopics;
                $lesson->total_topics_count = $totalTopics;

                $lesson->progress = $totalTopics > 0
                    ? round(($completedTopics / $totalTopics) * 100)
                    : 0;

                $lesson->next_topic = $allTopics
                    ->first(fn ($topic) => ! in_array($topic->id, $completedTopicIds));

                $topicsCompleted = $totalTopics > 0 && $completedTopics >= $totalTopics;

                $finalQuiz = $lesson->finalQuiz;

                $finalQuizPassed = true;

                if ($finalQuiz && $finalQuiz->is_required) {
                    $finalQuizPassed = QuizResult::where('user_id', $user->id)
                        ->where('quiz_id', $finalQuiz->id)
                        ->where('passed', true)
                        ->exists();
                }

                $lesson->final_quiz = $finalQuiz;
                $lesson->final_quiz_required = (bool) ($finalQuiz?->is_required);
                $lesson->final_quiz_passed = $finalQuizPassed;
                $lesson->can_generate_certificate = $topicsCompleted && $finalQuizPassed;
                $lesson->certificate = $certificates->get($lesson->id);

                return $lesson;
            });

        $totalLessons = $lessons->count();

        $completedLessons = $lessons
            ->filter(fn ($lesson) => $lesson->can_generate_certificate)
            ->count();

        $overallProgress = $totalLessons > 0
            ? round($lessons->avg('progress'))
            : 0;

        $quizResults = QuizResult::with('quiz')
            ->where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->latest()
            ->take(10)
            ->get();

        $quizAttempts = QuizResult::where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->count();

        $passedQuizzes = QuizResult::where('user_id', $user->id)
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