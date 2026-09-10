<?php

namespace Tests\Feature;

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\QuizController;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
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

class LessonCompletionTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Test Helpers
    |--------------------------------------------------------------------------
    */

    private function createLesson(
        string $title = 'Test Lesson',
        ?Lesson $prerequisite = null
    ): Lesson {
        return Lesson::create([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(8),
            'is_published' => true,
            'prerequisite_lesson_id' => $prerequisite?->id,
        ]);
    }

    private function createModule(
        Lesson $lesson,
        string $title = 'Module One',
        int $order = 1
    ): Module {
        return Module::create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'order' => $order,
            'is_published' => true,
        ]);
    }

    private function createTopic(
        Module $module,
        string $title = 'Topic One',
        int $order = 1
    ): LessonTopic {
        return LessonTopic::forceCreate([
            'module_id' => $module->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(8),
            'order' => $order,
            'is_published' => true,
        ]);
    }

    private function completeTopic(
        User $user,
        Lesson $lesson,
        LessonTopic $topic
    ): LessonProgress {
        return LessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic->id,
            'completed_at' => now(),
        ]);
    }

    private function createRequiredModuleQuiz(
        Lesson $lesson,
        Module $module,
        string $title = 'Module Quiz',
        int $passMark = 70
    ): Quiz {
        return Quiz::create([
            'lesson_id' => $lesson->id,
            'module_id' => $module->id,
            'lesson_topic_id' => null,
            'title' => $title,
            'quiz_type' => 'kupimwa',
            'pass_mark' => $passMark,
            'is_required' => true,
            'is_published' => true,
        ]);
    }

    private function attemptQuiz(
        User $user,
        Quiz $quiz,
        bool $passed = false
    ): QuizAttempt {
        return QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $passed ? 80 : 20,
            'correct_answers' => $passed ? 4 : 1,
            'total_questions' => 5,
            'passed' => $passed,
        ]);
    }

    private function enrollUser(
        User $user,
        Lesson $lesson
    ): LessonEnrollment {
        return LessonEnrollment::forceCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
            'study_pace' => Lesson::PACE_REGULAR,
            'study_hours_per_week' => 3,
            'schedule_started_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Tests
    |--------------------------------------------------------------------------
    */

    public function test_module_without_required_quiz_completes_after_all_topics_are_completed(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson();

        $module = $this->createModule($lesson);

        $topic = $this->createTopic($module);

        $this->assertFalse(
            $module->isCompletedBy($user)
        );

        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->assertTrue(
            $module->fresh()->isCompletedBy($user)
        );
    }

    public function test_module_with_required_quiz_stays_incomplete_until_quiz_is_attempted(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Quiz Lesson'
        );

        $module = $this->createModule(
            $lesson
        );

        $topic = $this->createTopic(
            $module
        );

        $quiz = $this->createRequiredModuleQuiz(
            $lesson,
            $module
        );

        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->assertFalse(
            $module->fresh()->isCompletedBy($user)
        );

        $this->assertSame(
            'quiz_pending',
            $module->fresh()->completionStatusFor($user)
        );

        $this->attemptQuiz(
            $user,
            $quiz,
            false
        );

        $this->assertTrue(
            $module->fresh()->isCompletedBy($user)
        );
    }

    public function test_module_quiz_does_not_need_to_be_passed_for_module_completion(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Attempt Rule Lesson'
        );

        $module = $this->createModule(
            $lesson
        );

        $topic = $this->createTopic(
            $module
        );

        $quiz = $this->createRequiredModuleQuiz(
            $lesson,
            $module,
            'Required Module Quiz',
            80
        );

        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->attemptQuiz(
            $user,
            $quiz,
            false
        );

        $this->assertTrue(
            $module->fresh()->isCompletedBy($user)
        );
    }

    public function test_lesson_stays_incomplete_when_a_required_module_quiz_has_not_been_attempted(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Full Lesson'
        );

        $module = $this->createModule(
            $lesson
        );

        $topic = $this->createTopic(
            $module
        );

        $this->createRequiredModuleQuiz(
            $lesson,
            $module
        );

        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->assertFalse(
            $module->fresh()->isCompletedBy($user)
        );

        $this->assertFalse(
            $lesson->fresh()->isCompletedBy($user)
        );
    }

    public function test_required_final_quiz_must_be_passed_before_lesson_is_completed(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Final Quiz Lesson'
        );

        $module = $this->createModule(
            $lesson
        );

        $topic = $this->createTopic(
            $module
        );

        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $finalQuiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'module_id' => null,
            'lesson_topic_id' => null,
            'title' => 'Final Quiz',
            'quiz_type' => 'kupimwa',
            'pass_mark' => 70,
            'is_required' => true,
            'is_published' => true,
        ]);

        $this->assertFalse(
            $lesson->fresh()->isCompletedBy($user)
        );

        QuizResult::create([
            'quiz_id' => $finalQuiz->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'lesson_topic_id' => null,
            'score' => 80,
            'correct' => 8,
            'total' => 10,
            'passed' => true,
        ]);

        $this->assertTrue(
            $lesson->fresh()->isCompletedBy($user)
        );
    }

    public function test_prerequisite_lesson_remains_locked_until_previous_lesson_is_completed(): void
    {
        $user = User::factory()->create();

        $firstLesson = $this->createLesson(
            'First Lesson'
        );

        $secondLesson = $this->createLesson(
            'Second Lesson',
            $firstLesson
        );

        $module = $this->createModule(
            $firstLesson
        );

        $topic = $this->createTopic(
            $module
        );

        $this->assertFalse(
            $firstLesson->fresh()->isCompletedBy($user)
        );

        $this->assertFalse(
            $secondLesson->fresh()->canBeStartedBy($user)
        );

        $this->completeTopic(
            $user,
            $firstLesson,
            $topic
        );

        $this->assertTrue(
            $firstLesson->fresh()->isCompletedBy($user)
        );

        $this->assertTrue(
            $secondLesson->fresh()->canBeStartedBy($user)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Flow Tests
    |--------------------------------------------------------------------------
    */

    public function test_student_can_open_later_module_even_when_previous_module_is_incomplete(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Open Module Navigation Lesson'
        );

        $moduleOne = $this->createModule(
            $lesson,
            'Module One',
            1
        );

        $topicOne = $this->createTopic(
            $moduleOne,
            'Topic One',
            1
        );

        $this->createRequiredModuleQuiz(
            $lesson,
            $moduleOne,
            'Module One Quiz'
        );

        $moduleTwo = $this->createModule(
            $lesson,
            'Module Two',
            2
        );

        $topicTwo = $this->createTopic(
            $moduleTwo,
            'Topic Two',
            1
        );

        $this->enrollUser(
            $user,
            $lesson
        );

        /*
        |--------------------------------------------------------------------------
        | Module 1 remains incomplete
        |--------------------------------------------------------------------------
        |
        | Topic finished, but required module quiz has not been attempted.
        |
        */
        $this->completeTopic(
            $user,
            $lesson,
            $topicOne
        );

        $this->assertFalse(
            $moduleOne->fresh()->isCompletedBy($user)
        );

        /*
        |--------------------------------------------------------------------------
        | Student can still open Module 2
        |--------------------------------------------------------------------------
        */
        $response = $this
            ->actingAs($user)
            ->get(
                route('lessons.learn', [
                    'lesson' => $lesson->slug,
                    'topic' => $topicTwo->id,
                ])
            );

        $response->assertOk();

        $response->assertViewHas(
            'currentTopic',
            fn ($currentTopic) =>
                $currentTopic
                && $currentTopic->id === $topicTwo->id
        );
    }

    public function test_final_quiz_is_blocked_until_all_modules_are_completed(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Final Quiz Access Lesson'
        );

        $module = $this->createModule(
            $lesson,
            'Module One',
            1
        );

        $topic = $this->createTopic(
            $module,
            'Topic One',
            1
        );

        $moduleQuiz = $this->createRequiredModuleQuiz(
            $lesson,
            $module,
            'Required Module Quiz'
        );

        $finalQuiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'module_id' => null,
            'lesson_topic_id' => null,
            'title' => 'Final Quiz',
            'quiz_type' => 'kupimwa',
            'pass_mark' => 70,
            'is_required' => true,
            'is_published' => true,
        ]);

        $this->enrollUser(
            $user,
            $lesson
        );

        /*
        |--------------------------------------------------------------------------
        | Complete topic but leave module quiz pending
        |--------------------------------------------------------------------------
        */
        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->assertFalse(
            $module->fresh()->isCompletedBy($user)
        );

        /*
        |--------------------------------------------------------------------------
        | Final quiz must remain locked
        |--------------------------------------------------------------------------
        */
        $response = $this
            ->actingAs($user)
            ->get(
                action(
                    [QuizController::class, 'show'],
                    ['quiz' => $finalQuiz->id]
                )
            );

        $response->assertRedirect(
            route(
                'lessons.learn',
                $lesson->slug
            )
        );

        $response->assertSessionHas('error');

        /*
        |--------------------------------------------------------------------------
        | Attempt module quiz
        |--------------------------------------------------------------------------
        |
        | It may be failed. The current rule only requires an attempt.
        |
        */
        $this->attemptQuiz(
            $user,
            $moduleQuiz,
            false
        );

        $this->assertTrue(
            $module->fresh()->isCompletedBy($user)
        );

        /*
        |--------------------------------------------------------------------------
        | Final quiz should now be accessible
        |--------------------------------------------------------------------------
        */
        $response = $this
            ->actingAs($user)
            ->get(
                action(
                    [QuizController::class, 'show'],
                    ['quiz' => $finalQuiz->id]
                )
            );

        $response->assertOk();
    }

    public function test_certificate_is_blocked_while_required_module_quiz_is_pending(): void
    {
        $user = User::factory()->create();

        $lesson = $this->createLesson(
            'Certificate Protection Lesson'
        );

        $module = $this->createModule(
            $lesson,
            'Module One',
            1
        );

        $topic = $this->createTopic(
            $module,
            'Topic One',
            1
        );

        $moduleQuiz = $this->createRequiredModuleQuiz(
            $lesson,
            $module,
            'Certificate Module Quiz'
        );

        $this->enrollUser(
            $user,
            $lesson
        );

        /*
        |--------------------------------------------------------------------------
        | Finish topic but leave module quiz pending
        |--------------------------------------------------------------------------
        */
        $this->completeTopic(
            $user,
            $lesson,
            $topic
        );

        $this->assertFalse(
            $lesson->fresh()->isCompletedBy($user)
        );

        /*
        |--------------------------------------------------------------------------
        | Direct certificate request must not bypass completion
        |--------------------------------------------------------------------------
        */
        $response = $this
            ->actingAs($user)
            ->post(
                action(
                    [CertificateController::class, 'issue'],
                    ['lesson' => $lesson->id]
                )
            );

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing(
            'certificates',
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Attempt required module quiz
        |--------------------------------------------------------------------------
        */
        $this->attemptQuiz(
            $user,
            $moduleQuiz,
            false
        );

        $this->assertTrue(
            $lesson->fresh()->isCompletedBy($user)
        );

        /*
        |--------------------------------------------------------------------------
        | Certificate should now be allowed
        |--------------------------------------------------------------------------
        */
        $response = $this
            ->actingAs($user)
            ->post(
                action(
                    [CertificateController::class, 'issue'],
                    ['lesson' => $lesson->id]
                )
            );

        $this->assertDatabaseHas(
            'certificates',
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]
        );
    }
}