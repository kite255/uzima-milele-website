<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use App\Models\QuizResult;
use Illuminate\Http\Request;

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
            ->with(['instructor', 'enrollments'])
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

        $categories = Lesson::where('is_published', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $levels = Lesson::where('is_published', true)
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
            'enrollments',
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

        $isEnrolled = auth()->check()
            ? $lesson->enrollments->contains('user_id', auth()->id())
            : false;

        return view('lessons.show', compact(
            'lesson',
            'allTopics',
            'totalTopics',
            'modulesCount',
            'questionsCount',
            'isEnrolled'
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

        $isEnrolled = LessonEnrollment::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo hili kwanza ili uanze kujifunza.');
        }

        $lesson->load([
            'instructor',
            'enrollments',

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

        $completedTopicIds = LessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->pluck('lesson_topic_id')
            ->toArray();

        $completedTopicsCount = count(array_unique($completedTopicIds));

        $totalTopics = $allTopics->count();

        $progressPercent = $totalTopics > 0
            ? round(($completedTopicsCount / $totalTopics) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Final quiz + certificate status
        |--------------------------------------------------------------------------
        */
        $allTopicsCompleted = $totalTopics > 0 && $completedTopicsCount >= $totalTopics;

        $finalQuiz = $lesson->finalQuiz;

        $finalQuizPassed = true;

        if ($finalQuiz) {
            $finalQuizPassed = QuizResult::where('user_id', auth()->id())
                ->where('quiz_id', $finalQuiz->id)
                ->where('passed', true)
                ->exists();
        }

        $certificate = Certificate::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->first();

        $canGenerateCertificate = $allTopicsCompleted
            && (! $finalQuiz || $finalQuizPassed)
            && ! $certificate;

        return view('lessons.learn', compact(
            'lesson',
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
    | Manual Enrollment Fallback
    |--------------------------------------------------------------------------
    */
    public function enroll(Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        LessonEnrollment::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'lesson_id' => $lesson->id,
            ],
            [
                'enrolled_at' => now(),
            ]
        );

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with('success', 'Umejiunga na somo hili. Karibu ujifunze.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Topic as Complete
    |--------------------------------------------------------------------------
    */
    public function markProgress(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $isEnrolled = LessonEnrollment::where('user_id', auth()->id())
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

        LessonProgress::firstOrCreate(
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
}