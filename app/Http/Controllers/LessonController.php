<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public Lessons List
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $level = $request->query('level');

        $lessons = Lesson::query()
            ->where('is_published', true)
            ->with([
                'instructor',
                'prerequisiteLesson',
            ])
            ->withCount('enrollments')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->when(
                $category,
                fn ($query) => $query->where('category', $category)
            )
            ->when(
                $level,
                fn ($query) => $query->where('level', $level)
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = Lesson::query()
            ->where('is_published', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $levels = Lesson::query()
            ->where('is_published', true)
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level');

        $enrolledLessonIds = auth()->check()
            ? auth()->user()
                ->lessonEnrollments()
                ->pluck('lesson_id')
                ->toArray()
            : [];

        return view('lessons.index', compact(
            'lessons',
            'categories',
            'levels',
            'search',
            'category',
            'level',
            'enrolledLessonIds'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Public Lesson Overview Page
    |--------------------------------------------------------------------------
    */
    public function show(Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $lesson->load([
            'instructor',
            'prerequisiteLesson',
            'publishedQuestions.user',
            'publishedQuestions.answeredBy',

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
        ]);

        $allTopics = $lesson->modules
            ->flatMap(fn ($module) => $module->topics)
            ->values();

        $totalTopics = $allTopics->count();
        $modulesCount = $lesson->modules->count();

        $topicQuestionsCount = $allTopics
            ->filter(fn ($topic) => $topic->quiz)
            ->flatMap(fn ($topic) => $topic->quiz->questions)
            ->count();

        $moduleQuestionsCount = $lesson->modules
            ->flatMap(fn ($module) => $module->quizzes)
            ->flatMap(fn ($quiz) => $quiz->questions)
            ->count();

        $finalQuestionsCount = $lesson->finalQuiz
            ? $lesson->finalQuiz->questions->count()
            : 0;

        $questionsCount =
            $topicQuestionsCount
            + $moduleQuestionsCount
            + $finalQuestionsCount;

        $enrollment = auth()->check()
            ? LessonEnrollment::query()
                ->where('user_id', auth()->id())
                ->where('lesson_id', $lesson->id)
                ->first()
            : null;

        $isEnrolled = (bool) $enrollment;

        $canStartLesson = $lesson->canBeStartedBy(auth()->user());

        $isLocked = ! $canStartLesson;

        return view('lessons.show', compact(
            'lesson',
            'allTopics',
            'totalTopics',
            'modulesCount',
            'questionsCount',
            'enrollment',
            'isEnrolled',
            'canStartLesson',
            'isLocked'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Protected Learning Page
    | URL: /lessons/{lesson:slug}/learn
    |--------------------------------------------------------------------------
    */
    public function learn(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $lesson->load('prerequisiteLesson');

        /*
        |--------------------------------------------------------------------------
        | Lesson-Level Prerequisite
        |--------------------------------------------------------------------------
        |
        | This only controls whether the whole lesson may be started.
        | It does NOT lock modules inside the lesson.
        |
        */
        if (! $lesson->canBeStartedBy($user)) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Somo hili limefungwa. Tafadhali kamilisha kwanza somo lililotangulia: ' .
                    ($lesson->prerequisiteLesson?->title ?? 'somo la awali') .
                    '.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Enrollment
        |--------------------------------------------------------------------------
        */
        $enrollment = LessonEnrollment::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->with('lesson')
            ->first();

        if (! $enrollment) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Tafadhali jiunge na somo hili kwanza ili uanze kujifunza.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Lesson Structure
        |--------------------------------------------------------------------------
        */
        $lesson->load([
            'instructor',
            'prerequisiteLesson',

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
        ]);

        /*
        |--------------------------------------------------------------------------
        | All Published Topics
        |--------------------------------------------------------------------------
        */
        $allTopics = $lesson->modules
            ->flatMap(fn ($module) => $module->topics)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Current Topic
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | There is intentionally no previous-module completion check here.
        |
        | A student can open any published topic/module in this lesson.
        |
        */
        $currentTopic = null;

        if ($request->filled('topic')) {
            $currentTopic = $allTopics->firstWhere(
                'id',
                (int) $request->topic
            );
        }

        if (! $currentTopic) {
            $currentTopic = $allTopics->first();
        }

        $currentIndex = $currentTopic
            ? $allTopics->search(
                fn ($topic) => $topic->id === $currentTopic->id
            )
            : false;

        $previousTopic =
            $currentIndex !== false && $currentIndex > 0
                ? $allTopics[$currentIndex - 1]
                : null;

        $nextTopic =
            $currentIndex !== false
            && $currentIndex < $allTopics->count() - 1
                ? $allTopics[$currentIndex + 1]
                : null;

        /*
        |--------------------------------------------------------------------------
        | Topic Progress
        |--------------------------------------------------------------------------
        */
        $topicIds = $allTopics->pluck('id');

        $completedTopicIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereIn('lesson_topic_id', $topicIds)
            ->pluck('lesson_topic_id')
            ->unique()
            ->values()
            ->toArray();

        $completedTopicsCount = count($completedTopicIds);

        $totalTopics = $allTopics->count();

        $progressPercent = $totalTopics > 0
            ? (int) round(
                ($completedTopicsCount / $totalTopics) * 100
            )
            : 0;

        $progressPercent = min(
            100,
            max(0, $progressPercent)
        );

        $allTopicsCompleted =
            $totalTopics > 0
            && $completedTopicsCount >= $totalTopics;

        /*
        |--------------------------------------------------------------------------
        | Module Completion
        |--------------------------------------------------------------------------
        |
        | Student navigation remains open.
        |
        | Module::isCompletedBy() determines completion:
        |
        | 1. Every published topic completed
        | 2. Required module quiz attempted
        |
        */
        $completedModulesCount = 0;
        $incompleteModulesCount = 0;
        $modulesWithQuizPending = 0;

        foreach ($lesson->modules as $module) {
            $module->progress =
                $module->progressPercentageFor($user);

            $module->completion_status =
                $module->completionStatusFor($user);

            $module->completion_label =
                $module->completionLabelFor($user);

            $module->is_completed =
                $module->isCompletedBy($user);

            $module->required_quiz =
                $module->requiredPublishedQuiz();

            $module->required_quiz_attempted =
                $module->hasRequiredQuizAttemptBy($user);

            $module->required_quiz_passed =
                $module->isRequiredQuizPassedBy($user);

            if ($module->is_completed) {
                $completedModulesCount++;
            } else {
                $incompleteModulesCount++;
            }

            if ($module->completion_status === 'quiz_pending') {
                $modulesWithQuizPending++;
            }
        }

        $totalModules = $lesson->modules->count();

        $allModulesCompleted =
            $totalModules > 0
            && $completedModulesCount >= $totalModules;

        /*
        |--------------------------------------------------------------------------
        | Final Quiz
        |--------------------------------------------------------------------------
        |
        | Final quiz only affects whole-lesson completion when it is required.
        |
        */
        $finalQuiz = $lesson->finalQuiz;

        $finalQuizRequired =
            (bool) ($finalQuiz?->is_required);

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
        | Lesson Completion
        |--------------------------------------------------------------------------
        |
        | Whole lesson completion:
        |
        | All modules complete
        | +
        | Required final quiz passed
        |
        */
        $lessonCompleted =
            $allModulesCompleted
            && $finalQuizPassed;

        /*
        |--------------------------------------------------------------------------
        | Certificate
        |--------------------------------------------------------------------------
        */
        $certificate = Certificate::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $canGenerateCertificate =
            $lessonCompleted
            && ! $certificate;

        /*
        |--------------------------------------------------------------------------
        | Pass Variables to Learning View
        |--------------------------------------------------------------------------
        */
        return view('lessons.learn', compact(
            'lesson',
            'enrollment',

            'currentTopic',
            'previousTopic',
            'nextTopic',
            'allTopics',

            'completedTopicIds',
            'completedTopicsCount',
            'totalTopics',
            'progressPercent',
            'allTopicsCompleted',

            'completedModulesCount',
            'incompleteModulesCount',
            'totalModules',
            'allModulesCompleted',
            'modulesWithQuizPending',

            'finalQuiz',
            'finalQuizRequired',
            'finalQuizPassed',

            'lessonCompleted',
            'certificate',
            'canGenerateCertificate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Enroll Student With Learning Schedule
    |--------------------------------------------------------------------------
    */
    public function enroll(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy($user)) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Huwezi kuanza somo hili bado. Tafadhali kamilisha kwanza somo lililotangulia: ' .
                    ($lesson->prerequisiteLesson?->title ?? 'somo la awali') .
                    '.'
                );
        }

        $validated = $request->validate([
            'study_pace' => [
                'nullable',
                'string',
                Rule::in([
                    Lesson::PACE_RELAXED,
                    Lesson::PACE_REGULAR,
                    Lesson::PACE_INTENSIVE,
                    Lesson::PACE_CUSTOM,
                ]),
            ],

            'study_hours_per_week' => [
                'nullable',
                'integer',
                'min:1',
                'max:40',
            ],
        ]);

        $pace =
            $validated['study_pace']
            ?? $lesson->default_study_pace
            ?? Lesson::PACE_REGULAR;

        $customHours =
            $pace === Lesson::PACE_CUSTOM
                ? (int) (
                    $validated['study_hours_per_week']
                    ?? $lesson->getPaceHours(
                        Lesson::PACE_REGULAR
                    )
                )
                : null;

        $existingEnrollment = LessonEnrollment::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with(
                    'success',
                    'Tayari umejiunga na somo hili. Karibu uendelee kujifunza.'
                );
        }

        LessonEnrollment::createForLesson(
            user: $user,
            lesson: $lesson,
            pace: $pace,
            customHours: $customHours
        );

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with(
                'success',
                'Umejiunga na somo hili. Ratiba yako ya kujifunza imeandaliwa.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Topic as Complete
    |--------------------------------------------------------------------------
    |
    | Normal topic:
    | - May be marked complete manually.
    |
    | Topic with REQUIRED published quiz:
    | - Cannot be manually completed until that quiz has been passed.
    |
    | This prevents a required topic quiz from being bypassed.
    |
    | This restriction does NOT prevent the student from opening later topics
    | or modules.
    |
    */
    public function markProgress(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy($user)) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Somo hili limefungwa. Kamilisha kwanza somo lililotangulia.'
                );
        }

        $isEnrolled = LessonEnrollment::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Tafadhali jiunge na somo hili kwanza.'
                );
        }

        $validated = $request->validate([
            'lesson_topic_id' => [
                'required',
                'integer',
                'exists:lesson_topics,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify Topic Belongs to This Published Lesson
        |--------------------------------------------------------------------------
        */
        $topic = LessonTopic::query()
            ->where('id', $validated['lesson_topic_id'])
            ->where('is_published', true)
            ->whereHas('module', function ($query) use ($lesson) {
                $query
                    ->where('lesson_id', $lesson->id)
                    ->where('is_published', true);
            })
            ->with([
                'quiz' => fn ($query) => $query
                    ->where('is_published', true),
                'module',
            ])
            ->first();

        if (! $topic) {
            return back()
                ->with(
                    'error',
                    'Mada hii si sehemu ya somo hili au haijachapishwa.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Required Topic Quiz
        |--------------------------------------------------------------------------
        |
        | A required topic quiz must be passed before that topic can be marked
        | complete.
        |
        | Student is still allowed to continue studying other topics/modules.
        |
        */
        $topicQuiz = $topic->quiz;

        if (
            $topicQuiz
            && $topicQuiz->is_published
            && $topicQuiz->is_required
        ) {
            $topicQuizPassed = QuizResult::query()
                ->where('user_id', $user->id)
                ->where('quiz_id', $topicQuiz->id)
                ->where('passed', true)
                ->exists();

            if (! $topicQuizPassed) {
                return back()->with(
                    'error',
                    'Mada hii ina quiz ya lazima. Unaweza kuendelea na mada nyingine, lakini mada hii itabaki haijakamilika mpaka ufaulu quiz yake.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Store Topic Completion
        |--------------------------------------------------------------------------
        */
        LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'lesson_topic_id' => $topic->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        return back()->with(
            'success',
            'Mada imewekwa kama imekamilika.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Learning Schedule
    |--------------------------------------------------------------------------
    */
    public function resetSchedule(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy($user)) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Somo hili limefungwa. Kamilisha kwanza somo lililotangulia.'
                );
        }

        $enrollment = LessonEnrollment::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->with('lesson')
            ->firstOrFail();

        if (! $enrollment->canResetSchedule()) {
            return back()
                ->with(
                    'error',
                    'Samahani, mfumo hauruhusu kubadili ratiba ya somo hili.'
                );
        }

        $validated = $request->validate([
            'study_pace' => [
                'required',
                'string',
                Rule::in([
                    Lesson::PACE_RELAXED,
                    Lesson::PACE_REGULAR,
                    Lesson::PACE_INTENSIVE,
                    Lesson::PACE_CUSTOM,
                ]),
            ],

            'study_hours_per_week' => [
                'nullable',
                'integer',
                'min:1',
                'max:40',
            ],
        ]);

        $pace = $validated['study_pace'];

        $customHours =
            $pace === Lesson::PACE_CUSTOM
                ? (int) (
                    $validated['study_hours_per_week']
                    ?? $lesson->getPaceHours(
                        Lesson::PACE_REGULAR
                    )
                )
                : null;

        $enrollment->resetSchedule(
            $pace,
            $customHours
        );

        return back()
            ->with(
                'success',
                'Ratiba yako ya kujifunza imebadilishwa.'
            );
    }
}