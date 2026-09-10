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
        | Vyeti vya mwanafunzi
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
        | Masomo ambayo mwanafunzi amejiunga nayo
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
                'prerequisiteLesson',

                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics.quiz' => fn ($query) => $query
                    ->where('is_published', true),

                'modules.topics.quiz.questions' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),

                'modules.quizzes' => fn ($query) => $query
                    ->where('is_published', true),

                'modules.quizzes.questions' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),

                'finalQuiz' => fn ($query) => $query
                    ->where('is_published', true),

                'finalQuiz.questions' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->where('lessons.is_published', true)
            ->orderByPivot('enrolled_at', 'desc')
            ->get()
            ->map(function ($lesson) use ($user, $certificates) {

                /*
                |--------------------------------------------------------------------------
                | Mada zote za somo
                |--------------------------------------------------------------------------
                */
                $allTopics = $lesson->modules
                    ->flatMap(fn ($module) => $module->topics)
                    ->values();

                $topicIds = $allTopics->pluck('id');

                $totalTopics = $allTopics->count();

                /*
                |--------------------------------------------------------------------------
                | Mada zilizokamilika
                |--------------------------------------------------------------------------
                */
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
                | Hali ya kila module
                |--------------------------------------------------------------------------
                |
                | Student can continue to another module even when a previous
                | module has not been completed.
                |
                | Module completion itself is determined by Module::isCompletedBy():
                |
                | - All published topics completed
                | - Required module quiz attempted
                |
                */
                $completedModules = 0;
                $incompleteModules = 0;
                $modulesWithQuizPending = 0;

                foreach ($lesson->modules as $module) {
                    $module->progress = $module->progressPercentageFor($user);
                    $module->completion_status = $module->completionStatusFor($user);
                    $module->completion_label = $module->completionLabelFor($user);
                    $module->is_completed = $module->isCompletedBy($user);

                    $module->required_quiz = $module->requiredPublishedQuiz();
                    $module->required_quiz_attempted = $module->hasRequiredQuizAttemptBy($user);

                    if ($module->is_completed) {
                        $completedModules++;
                    } else {
                        $incompleteModules++;
                    }

                    if ($module->completion_status === 'quiz_pending') {
                        $modulesWithQuizPending++;
                    }
                }

                $totalModules = $lesson->modules->count();

                $lesson->total_modules_count = $totalModules;
                $lesson->completed_modules_count = $completedModules;
                $lesson->incomplete_modules_count = $incompleteModules;
                $lesson->modules_with_quiz_pending = $modulesWithQuizPending;

                /*
                |--------------------------------------------------------------------------
                | Module completion
                |--------------------------------------------------------------------------
                |
                | The lesson's modules are considered complete only when every
                | published module is complete.
                */
                $allModulesCompleted = $totalModules > 0
                    && $completedModules >= $totalModules;

                $lesson->all_modules_completed = $allModulesCompleted;

                /*
                |--------------------------------------------------------------------------
                | Mada inayofuata
                |--------------------------------------------------------------------------
                |
                | Topic navigation is not blocked by module completion.
                */
                $lesson->next_topic = $allTopics
                    ->first(fn ($topic) => ! in_array(
                        $topic->id,
                        $completedTopicIds,
                        true
                    ));

                /*
                |--------------------------------------------------------------------------
                | Jaribio la mwisho
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
                | Cheti cha somo
                |--------------------------------------------------------------------------
                */
                $certificate = $certificates->get($lesson->id);

                $lesson->certificate = $certificate;
                $lesson->final_quiz = $finalQuiz;
                $lesson->final_quiz_required = $finalQuizRequired;
                $lesson->final_quiz_passed = $finalQuizPassed;

                /*
                |--------------------------------------------------------------------------
                | Lesson completion
                |--------------------------------------------------------------------------
                |
                | Whole lesson completion:
                |
                | - Every published module completed
                | - Final quiz passed if required
                |
                */
                $lesson->is_completed = $allModulesCompleted
                    && $finalQuizPassed;

                $lesson->can_generate_certificate = $lesson->is_completed
                    && ! $certificate;

                /*
                |--------------------------------------------------------------------------
                | Prerequisite lesson lock
                |--------------------------------------------------------------------------
                |
                | This remains lesson-level prerequisite logic.
                |
                | IMPORTANT:
                | It does NOT lock movement from one module to another inside
                | the same lesson.
                */
                $lesson->can_start = method_exists($lesson, 'canBeStartedBy')
                    ? $lesson->canBeStartedBy($user)
                    : true;

                $lesson->is_locked = ! $lesson->can_start;

                $lesson->prerequisite_title = $lesson->prerequisiteLesson
                    ? $lesson->prerequisiteLesson->title
                    : null;

                /*
                |--------------------------------------------------------------------------
                | Dashboard status
                |--------------------------------------------------------------------------
                */
                if ($lesson->is_locked) {
                    $lesson->status_label = 'Limefungwa';
                    $lesson->status_color = 'yellow';

                    $lesson->status_message = $lesson->prerequisite_title
                        ? 'Kamilisha kwanza somo la awali: ' . $lesson->prerequisite_title
                        : 'Kamilisha somo la awali ili kufungua somo hili.';
                } elseif ($lesson->is_completed) {
                    $lesson->status_label = 'Limekamilika';
                    $lesson->status_color = 'green';
                    $lesson->status_message = 'Hongera, umekamilisha somo hili.';
                } elseif ($modulesWithQuizPending > 0) {
                    $lesson->status_label = 'Quiz Inasubiri';
                    $lesson->status_color = 'yellow';

                    $lesson->status_message = $modulesWithQuizPending === 1
                        ? 'Kuna module 1 ambayo mada zake zimekamilika lakini quiz bado haijafanywa.'
                        : 'Kuna modules ' . $modulesWithQuizPending . ' ambazo quiz zake bado hazijafanywa.';
                } elseif ($lesson->progress > 0) {
                    $lesson->status_label = 'Unaendelea';
                    $lesson->status_color = 'blue';
                    $lesson->status_message = 'Endelea kujifunza kutoka ulipoishia.';
                } else {
                    $lesson->status_label = 'Lipo wazi';
                    $lesson->status_color = 'blue';
                    $lesson->status_message = 'Unaweza kuanza somo hili.';
                }

                /*
                |--------------------------------------------------------------------------
                | Hatua inayofuata
                |--------------------------------------------------------------------------
                */
                if ($lesson->is_locked) {
                    $lesson->next_action_label = 'Kamilisha Somo la Awali';
                } elseif ($lesson->next_topic) {
                    $lesson->next_action_label = $lesson->progress > 0
                        ? 'Endelea Kujifunza'
                        : 'Anza Kujifunza';
                } elseif ($modulesWithQuizPending > 0) {
                    $lesson->next_action_label = 'Fanya Quiz ya Module';
                } elseif (
                    $finalQuizRequired
                    && ! $finalQuizPassed
                    && $finalQuiz
                ) {
                    $lesson->next_action_label = 'Fanya Jaribio la Mwisho';
                } elseif ($lesson->is_completed && $certificate) {
                    $lesson->next_action_label = 'Tazama Cheti';
                } elseif ($lesson->can_generate_certificate) {
                    $lesson->next_action_label = 'Tengeneza Cheti';
                } else {
                    $lesson->next_action_label = 'Tazama Somo';
                }

                return $lesson;
            });

        /*
        |--------------------------------------------------------------------------
        | Takwimu za dashboard
        |--------------------------------------------------------------------------
        */
        $totalLessons = $lessons->count();

        $completedLessons = $lessons
            ->filter(fn ($lesson) => $lesson->is_completed)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Overall learning progress
        |--------------------------------------------------------------------------
        |
        | This remains topic-based progress.
        |
        | Therefore a lesson may display 100% learning progress while its
        | status is "Quiz Inasubiri". That is intentional.
        |
        */
        $overallProgress = $totalLessons > 0
            ? (int) round($lessons->avg('progress'))
            : 0;

        $overallProgress = min(100, max(0, $overallProgress));

        /*
        |--------------------------------------------------------------------------
        | Matokeo ya majaribio
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