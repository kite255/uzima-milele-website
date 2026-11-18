<?php

namespace App\Http\Controllers;

use App\Models\LessonEnrollment;
use App\Models\Quiz;
use App\Services\GradeStudyQuiz;
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

    public function submit(Request $request, Quiz $quiz, GradeStudyQuiz $grader)
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

        $answers = $quiz->questions->mapWithKeys(
            fn ($question) => [$question->id => $request->input('question_' . $question->id)]
        )->all();
        ['result' => $result, 'review' => $review] = $grader->grade($quiz, $request->user(), $answers);

        /*
        |--------------------------------------------------------------------------
        | Result Message
        |--------------------------------------------------------------------------
        */
        $message = $result['passed']
            ? 'Hongera! Umefaulu quiz hii.'
            : 'Hujafaulu bado. Unaweza kurudia tena.';

        return back()
            ->withInput()
            ->with([
                'success' => $message,

                'result' => $result,

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