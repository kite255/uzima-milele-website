<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
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
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($level, fn ($query) => $query->where('level', $level))
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
            ? auth()->user()->lessonEnrollments()->pluck('lesson_id')->toArray()
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

        $questionsCount = $topicQuestionsCount + $moduleQuestionsCount + $finalQuestionsCount;

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

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy(auth()->user())) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Somo hili limefungwa. Tafadhali kamilisha kwanza somo lililotangulia: ' .
                    ($lesson->prerequisiteLesson?->title ?? 'somo la awali') . '.'
                );
        }

        $enrollment = LessonEnrollment::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->with('lesson')
            ->first();

        if (! $enrollment) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo hili kwanza ili uanze kujifunza.');
        }

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

        $allTopics = $lesson->modules
            ->flatMap(fn ($module) => $module->topics)
            ->values();

        $currentTopic = null;

        if ($request->filled('topic')) {
            $currentTopic = $allTopics->firstWhere('id', (int) $request->topic);
        }

        if (! $currentTopic) {
            $currentTopic = $allTopics->first();
        }

        $currentIndex = $currentTopic
            ? $allTopics->search(fn ($topic) => $topic->id === $currentTopic->id)
            : false;

        $previousTopic = $currentIndex !== false && $currentIndex > 0
            ? $allTopics[$currentIndex - 1]
            : null;

        $nextTopic = $currentIndex !== false && $currentIndex < $allTopics->count() - 1
            ? $allTopics[$currentIndex + 1]
            : null;

        $completedTopicIds = LessonProgress::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->pluck('lesson_topic_id')
            ->unique()
            ->toArray();

        $completedTopicsCount = count($completedTopicIds);

        $totalTopics = $allTopics->count();

        $progressPercent = $totalTopics > 0
            ? (int) round(($completedTopicsCount / $totalTopics) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Final Quiz + Certificate Status
        |--------------------------------------------------------------------------
        */
        $allTopicsCompleted = $totalTopics > 0 && $completedTopicsCount >= $totalTopics;

        $finalQuiz = $lesson->finalQuiz;

        $finalQuizPassed = true;

        if ($finalQuiz) {
            $finalQuizPassed = QuizResult::query()
                ->where('user_id', auth()->id())
                ->where('quiz_id', $finalQuiz->id)
                ->where('passed', true)
                ->exists();
        }

        $certificate = Certificate::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->first();

        $canGenerateCertificate = $allTopicsCompleted
            && (! $finalQuiz || $finalQuizPassed)
            && ! $certificate;

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
            'finalQuiz',
            'finalQuizPassed',
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

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy(auth()->user())) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Huwezi kuanza somo hili bado. Tafadhali kamilisha kwanza somo lililotangulia: ' .
                    ($lesson->prerequisiteLesson?->title ?? 'somo la awali') . '.'
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

        $pace = $validated['study_pace']
            ?? $lesson->default_study_pace
            ?? Lesson::PACE_REGULAR;

        $customHours = $pace === Lesson::PACE_CUSTOM
            ? (int) ($validated['study_hours_per_week'] ?? $lesson->getPaceHours(Lesson::PACE_REGULAR))
            : null;

        $existingEnrollment = LessonEnrollment::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with('success', 'Tayari umejiunga na somo hili. Karibu uendelee kujifunza.');
        }

        LessonEnrollment::createForLesson(
            user: auth()->user(),
            lesson: $lesson,
            pace: $pace,
            customHours: $customHours
        );

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with('success', 'Umejiunga na somo hili. Ratiba yako ya kujifunza imeandaliwa.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Topic as Complete
    |--------------------------------------------------------------------------
    */
    public function markProgress(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy(auth()->user())) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Somo hili limefungwa. Kamilisha kwanza somo lililotangulia.');
        }

        $isEnrolled = LessonEnrollment::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo hili kwanza.');
        }

        $validated = $request->validate([
            'lesson_topic_id' => ['required', 'integer', 'exists:lesson_topics,id'],
        ]);

        $topicBelongsToLesson = $lesson->topics()
            ->where('lesson_topics.id', $validated['lesson_topic_id'])
            ->exists();

        if (! $topicBelongsToLesson) {
            return back()
                ->with('error', 'Mada hii si sehemu ya somo hili.');
        }

        LessonProgress::query()->firstOrCreate(
            [
                'user_id' => auth()->id(),
                'lesson_id' => $lesson->id,
                'lesson_topic_id' => $validated['lesson_topic_id'],
            ],
            [
                'completed_at' => now(),
            ]
        );

        return back()->with('success', 'Mada imewekwa kama imekamilika.');
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Learning Schedule
    |--------------------------------------------------------------------------
    */
    public function resetSchedule(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $lesson->load('prerequisiteLesson');

        if (! $lesson->canBeStartedBy(auth()->user())) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Somo hili limefungwa. Kamilisha kwanza somo lililotangulia.');
        }

        $enrollment = LessonEnrollment::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->with('lesson')
            ->firstOrFail();

        if (! $enrollment->canResetSchedule()) {
            return back()
                ->with('error', 'Samahani, mfumo hauruhusu kubadili ratiba ya somo hili.');
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

        $customHours = $pace === Lesson::PACE_CUSTOM
            ? (int) ($validated['study_hours_per_week'] ?? $lesson->getPaceHours(Lesson::PACE_REGULAR))
            : null;

        $enrollment->resetSchedule($pace, $customHours);

        return back()
            ->with('success', 'Ratiba yako ya kujifunza imebadilishwa.');
    }
}