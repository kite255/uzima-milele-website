<?php

namespace App\Services;

use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GradeStudyQuiz
{
    public function grade(Quiz $quiz, User $student, array $answers): array
    {
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;
        $review = [];

        foreach ($quiz->questions as $question) {
            $userAnswer = $answers[$question->id];

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

        DB::transaction(function () use ($quiz, $student, $score, $correctAnswers, $totalQuestions, $passed) {
            QuizAttempt::create([
                'user_id' => $student->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'passed' => $passed,
            ]);

            QuizResult::create([
                'quiz_id' => $quiz->id,
                'user_id' => $student->id,
                'user_name' => $student->name,
                'lesson_topic_id' => $quiz->lesson_topic_id,
                'score' => $score,
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'passed' => $passed,
            ]);

            if (
                $passed
                && $quiz->lesson_topic_id
                && $quiz->topic?->module
            ) {
                LessonProgress::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'lesson_id' => $quiz->topic->module->lesson_id,
                        'lesson_topic_id' => $quiz->lesson_topic_id,
                    ],
                    [
                        'completed_at' => now(),
                    ]
                );
            }
        });

        return [
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
        ];
    }
}
