<?php

namespace App\Http\Controllers;

use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        if (! auth()->check()) {
            return redirect()
                ->route('login', ['redirect' => request()->fullUrl()]);
        }

        $quiz->load([
            'questions' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order'),

            'topic.module.lesson',
            'module.lesson',
            'lesson.modules.topics',
        ]);

        abort_if(! $quiz->is_published, 404);

        $lockResponse = $this->checkFinalQuizAccess($quiz);

        if ($lockResponse) {
            return $lockResponse;
        }

        return view('quiz.show', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        if (! auth()->check()) {
            return redirect()
                ->route('login', ['redirect' => request()->fullUrl()]);
        }

        $quiz->load([
            'questions' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order'),

            'topic.module.lesson',
            'module.lesson',
            'lesson.modules.topics',
        ]);

        abort_if(! $quiz->is_published, 404);

        $lockResponse = $this->checkFinalQuizAccess($quiz);

        if ($lockResponse) {
            return $lockResponse;
        }

        if ($quiz->questions->isEmpty()) {
            return back()->with('error', 'Quiz hii haina maswali yaliyowashwa.');
        }

        $rules = [];

        foreach ($quiz->questions as $question) {
            $rules['question_' . $question->id] = ['required'];
        }

        $request->validate($rules, [
            '*.required' => 'Tafadhali jibu maswali yote kabla ya kuwasilisha.',
        ]);

        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;
        $review = [];

        foreach ($quiz->questions as $question) {
            $userAnswer = $request->input('question_' . $question->id);
            $isCorrect = $question->isCorrect($userAnswer);

            if ($isCorrect) {
                $correctAnswers++;
            }

            $correctAnswer = $question->type === 'multiple_choice'
                ? $question->getCorrectOptionIndex()
                : $question->correct_answer;

            $review[$question->id] = [
                'user_answer' => (string) $userAnswer,
                'correct_answer' => (string) $correctAnswer,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
            ];
        }

        $score = round(($correctAnswers / $totalQuestions) * 100);
        $passed = $score >= (int) $quiz->pass_mark;

        QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'passed' => $passed,
        ]);

        QuizResult::create([
            'quiz_id' => $quiz->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'lesson_topic_id' => $quiz->lesson_topic_id,
            'score' => $score,
            'correct' => $correctAnswers,
            'total' => $totalQuestions,
            'passed' => $passed,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Completion Logic
        |--------------------------------------------------------------------------
        | Topic quiz:
        | - If passed, mark topic as completed.
        |
        | Final quiz:
        | - Does not mark a topic.
        | - Certificate logic will check if final quiz is passed.
        */
        if ($passed && $quiz->lesson_topic_id && $quiz->topic?->module) {
            LessonProgress::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'lesson_topic_id' => $quiz->lesson_topic_id,
                ],
                [
                    'lesson_id' => $quiz->topic->module->lesson_id,
                    'completed_at' => now(),
                ]
            );
        }

        $message = $passed
            ? 'Hongera! Umefaulu quiz hii.'
            : 'Hujafaulu bado. Unaweza kurudia tena.';

        return back()->withInput()->with([
            'success' => $message,
            'result' => [
                'score' => $score,
                'passed' => $passed,
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'pass_mark' => (int) $quiz->pass_mark,
                'quiz_type' => $quiz->quiz_type,
                'is_required' => (bool) $quiz->is_required,
            ],
            'review' => $review,
        ]);
    }

    private function checkFinalQuizAccess(Quiz $quiz)
    {
        /*
        |--------------------------------------------------------------------------
        | Final Quiz Lock
        |--------------------------------------------------------------------------
        | Final quiz = quiz attached directly to lesson only:
        | lesson_id exists, module_id is null, lesson_topic_id is null.
        */
        $isFinalQuiz = $quiz->lesson_id
            && is_null($quiz->module_id)
            && is_null($quiz->lesson_topic_id);

        if (! $isFinalQuiz) {
            return null;
        }

        $lesson = $quiz->lesson;

        if (! $lesson) {
            abort(404);
        }

        $isEnrolled = LessonEnrollment::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo hili kwanza.');
        }

        $totalTopics = $lesson->modules()
            ->where('is_published', true)
            ->withCount([
                'topics' => fn ($query) => $query->where('is_published', true),
            ])
            ->get()
            ->sum('topics_count');

        $completedTopics = LessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->distinct('lesson_topic_id')
            ->count('lesson_topic_id');

        if ($totalTopics > 0 && $completedTopics < $totalTopics) {
            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with('error', 'Lazima ukamilishe mada zote kabla ya kufanya jaribio la mwisho.');
        }

        return null;
    }
}