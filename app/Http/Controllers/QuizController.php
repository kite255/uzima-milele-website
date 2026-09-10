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
            'lesson.modules.quizzes',
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
            'lesson.modules.quizzes',
        ]);

        abort_if(! $quiz->is_published, 404);

        $lockResponse = $this->checkFinalQuizAccess($quiz);

        if ($lockResponse) {
            return $lockResponse;
        }

        if ($quiz->questions->isEmpty()) {
            return back()
                ->with('error', 'Quiz hii haina maswali yaliyowashwa.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $rules = [];

        foreach ($quiz->questions as $question) {
            $rules['question_' . $question->id] = ['required'];
        }

        $request->validate($rules, [
            '*.required' => 'Tafadhali jibu maswali yote kabla ya kuwasilisha.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Mark Quiz
        |--------------------------------------------------------------------------
        */
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;
        $review = [];

        foreach ($quiz->questions as $question) {
            $userAnswer = $request->input(
                'question_' . $question->id
            );

            $isCorrect = $question->isCorrect($userAnswer);

            if ($isCorrect) {
                $correctAnswers++;
            }

            $correctAnswer =
                $question->type === 'multiple_choice'
                    ? $question->getCorrectOptionIndex()
                    : $question->correct_answer;

            $review[$question->id] = [
                'user_answer' => (string) $userAnswer,
                'correct_answer' => (string) $correctAnswer,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
            ];
        }

        $score = round(
            ($correctAnswers / $totalQuestions) * 100
        );

        $passed =
            $score >= (int) $quiz->pass_mark;

        /*
        |--------------------------------------------------------------------------
        | Save Quiz Attempt
        |--------------------------------------------------------------------------
        |
        | This record is important for MODULE quizzes.
        |
        | Under the current module completion rule:
        |
        | - A required module quiz only needs to be attempted.
        | - It does not need to be passed for the module to count as completed.
        |
        */
        QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'passed' => $passed,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Quiz Result
        |--------------------------------------------------------------------------
        */
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
        | Topic Completion Logic
        |--------------------------------------------------------------------------
        |
        | Only TOPIC quizzes can directly mark a topic as completed.
        |
        | Required topic quiz:
        | - Must be passed.
        |
        | Module quiz:
        | - Does not create LessonProgress.
        | - QuizAttempt itself proves it was taken.
        |
        | Final quiz:
        | - Does not create LessonProgress.
        |
        */
        if (
            $passed
            && $quiz->lesson_topic_id
            && $quiz->topic?->module
        ) {
            LessonProgress::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'lesson_id' => $quiz->topic->module->lesson_id,
                    'lesson_topic_id' => $quiz->lesson_topic_id,
                ],
                [
                    'completed_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Result Message
        |--------------------------------------------------------------------------
        */
        $message = $passed
            ? 'Hongera! Umefaulu quiz hii.'
            : 'Hujafaulu bado. Unaweza kurudia tena.';

        return back()
            ->withInput()
            ->with([
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

    /*
    |--------------------------------------------------------------------------
    | Final Quiz Access
    |--------------------------------------------------------------------------
    */
    private function checkFinalQuizAccess(Quiz $quiz)
    {
        /*
        |--------------------------------------------------------------------------
        | Detect Final Quiz
        |--------------------------------------------------------------------------
        |
        | Final quiz:
        |
        | lesson_id exists
        | module_id is null
        | lesson_topic_id is null
        |
        */
        $isFinalQuiz =
            $quiz->lesson_id
            && is_null($quiz->module_id)
            && is_null($quiz->lesson_topic_id);

        /*
        |--------------------------------------------------------------------------
        | Topic Quiz / Module Quiz
        |--------------------------------------------------------------------------
        |
        | These quizzes are not locked by this method.
        |
        | The student may continue studying freely between modules.
        |
        */
        if (! $isFinalQuiz) {
            return null;
        }

        $user = auth()->user();

        $lesson = $quiz->lesson;

        if (! $lesson) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Enrollment Check
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Load Published Modules
        |--------------------------------------------------------------------------
        |
        | The final quiz must not become available until every published
        | module has satisfied Module::isCompletedBy().
        |
        */
        $modules = $lesson->modules()
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | No Published Modules
        |--------------------------------------------------------------------------
        */
        if ($modules->isEmpty()) {
            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with(
                    'error',
                    'Somo hili halina modules zilizochapishwa.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Incomplete Modules
        |--------------------------------------------------------------------------
        */
        $incompleteModules = $modules
            ->filter(
                fn ($module) => ! $module->isCompletedBy($user)
            );

        if ($incompleteModules->isNotEmpty()) {
            $quizPendingCount = $incompleteModules
                ->filter(
                    fn ($module) =>
                        $module->completionStatusFor($user) === 'quiz_pending'
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Better Feedback
            |--------------------------------------------------------------------------
            */
            if ($quizPendingCount > 0) {
                return redirect()
                    ->route('lessons.learn', $lesson->slug)
                    ->with(
                        'error',
                        $quizPendingCount === 1
                            ? 'Kuna module ambayo mada zake zimekamilika lakini quiz yake bado haijafanywa. Fanya quiz hiyo kabla ya jaribio la mwisho.'
                            : 'Kuna modules ' . $quizPendingCount . ' ambazo quiz zake bado hazijafanywa. Kamilisha quiz hizo kabla ya jaribio la mwisho.'
                    );
            }

            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with(
                    'error',
                    'Kamilisha modules zote kabla ya kufanya jaribio la mwisho.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Final Quiz Allowed
        |--------------------------------------------------------------------------
        */
        return null;
    }
}