<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleQuizCompletionRuleTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedModuleWithRequiredQuiz(User $user): array
    {
        $lesson = Lesson::create([
            'title' => 'Required Quiz Completion Rule',
            'slug' => 'required-quiz-' . Str::random(8),
            'is_published' => true,
        ]);

        $module = Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Module One',
            'order' => 1,
            'is_published' => true,
        ]);

        $topic = LessonTopic::forceCreate([
            'module_id' => $module->id,
            'title' => 'Topic One',
            'slug' => 'topic-' . Str::random(8),
            'order' => 1,
            'is_published' => true,
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'module_id' => $module->id,
            'lesson_topic_id' => null,
            'title' => 'Required Module Quiz',
            'quiz_type' => 'kupimwa',
            'pass_mark' => 70,
            'is_required' => true,
            'is_published' => true,
        ]);

        LessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic->id,
            'completed_at' => now(),
        ]);

        return [$lesson, $module, $quiz];
    }

    public function test_failed_required_module_quiz_keeps_module_incomplete(): void
    {
        $user = User::factory()->create();

        [, $module, $quiz] = $this->createCompletedModuleWithRequiredQuiz($user);

        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 20,
            'correct_answers' => 1,
            'total_questions' => 5,
            'passed' => false,
        ]);

        $this->assertFalse(
            $module->fresh()->isCompletedBy($user)
        );

        $this->assertSame(
            'quiz_failed',
            $module->fresh()->completionStatusFor($user)
        );
    }

    public function test_required_module_quiz_must_be_passed_before_module_completes(): void
    {
        $user = User::factory()->create();

        [, $module, $quiz] = $this->createCompletedModuleWithRequiredQuiz($user);

        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 80,
            'correct_answers' => 4,
            'total_questions' => 5,
            'passed' => true,
        ]);

        QuizResult::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'lesson_topic_id' => null,
            'score' => 80,
            'correct' => 4,
            'total' => 5,
            'passed' => true,
        ]);

        $this->assertTrue(
            $module->fresh()->isCompletedBy($user)
        );

        $this->assertSame(
            'completed',
            $module->fresh()->completionStatusFor($user)
        );
    }
}
