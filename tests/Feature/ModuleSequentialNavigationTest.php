<?php

namespace Tests\Feature;

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

class ModuleSequentialNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function createLesson(): Lesson
    {
        return Lesson::create([
            'title' => 'Sequential Module Lesson',
            'slug' => 'sequential-module-' . Str::random(8),
            'is_published' => true,
        ]);
    }

    private function createModule(Lesson $lesson, string $title, int $order): Module
    {
        return Module::create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'order' => $order,
            'is_published' => true,
        ]);
    }

    private function createTopic(Module $module, string $title, int $order = 1): LessonTopic
    {
        return LessonTopic::forceCreate([
            'module_id' => $module->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(8),
            'order' => $order,
            'is_published' => true,
        ]);
    }

    private function enroll(User $user, Lesson $lesson): void
    {
        LessonEnrollment::forceCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
            'study_pace' => Lesson::PACE_REGULAR,
            'study_hours_per_week' => 3,
            'schedule_started_at' => now(),
        ]);
    }

    private function completeTopic(User $user, Lesson $lesson, LessonTopic $topic): void
    {
        LessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic->id,
            'completed_at' => now(),
        ]);
    }

    private function createRequiredModuleQuiz(Lesson $lesson, Module $module): Quiz
    {
        return Quiz::create([
            'lesson_id' => $lesson->id,
            'module_id' => $module->id,
            'lesson_topic_id' => null,
            'title' => 'Required Module Quiz',
            'quiz_type' => 'kupimwa',
            'pass_mark' => 70,
            'is_required' => true,
            'is_published' => true,
        ]);
    }

    public function test_student_cannot_open_later_module_before_previous_module_is_complete(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $moduleOne = $this->createModule($lesson, 'Module One', 1);
        $topicOne = $this->createTopic($moduleOne, 'Topic One');
        $moduleTwo = $this->createModule($lesson, 'Module Two', 2);
        $topicTwo = $this->createTopic($moduleTwo, 'Topic Two');

        $this->enroll($user, $lesson);

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertRedirect(route('lessons.learn', [
            'lesson' => $lesson->slug,
            'topic' => $topicOne->id,
        ]));
        $response->assertSessionHas('error');
    }

    public function test_later_module_unlocks_after_previous_module_without_required_quiz_is_completed(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $moduleOne = $this->createModule($lesson, 'Module One', 1);
        $topicOne = $this->createTopic($moduleOne, 'Topic One');
        $moduleTwo = $this->createModule($lesson, 'Module Two', 2);
        $topicTwo = $this->createTopic($moduleTwo, 'Topic Two');

        $this->enroll($user, $lesson);
        $this->completeTopic($user, $lesson, $topicOne);

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertOk();
        $response->assertViewHas(
            'currentTopic',
            fn ($currentTopic) => $currentTopic?->id === $topicTwo->id
        );
    }

    public function test_failed_required_module_quiz_keeps_later_module_locked(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $moduleOne = $this->createModule($lesson, 'Module One', 1);
        $topicOne = $this->createTopic($moduleOne, 'Topic One');
        $quiz = $this->createRequiredModuleQuiz($lesson, $moduleOne);
        $moduleTwo = $this->createModule($lesson, 'Module Two', 2);
        $topicTwo = $this->createTopic($moduleTwo, 'Topic Two');

        $this->enroll($user, $lesson);
        $this->completeTopic($user, $lesson, $topicOne);

        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 20,
            'correct_answers' => 1,
            'total_questions' => 5,
            'passed' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertRedirect(route('lessons.learn', [
            'lesson' => $lesson->slug,
            'topic' => $topicOne->id,
        ]));
        $response->assertSessionHas('error');
    }

    public function test_passing_required_module_quiz_unlocks_later_module(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $moduleOne = $this->createModule($lesson, 'Module One', 1);
        $topicOne = $this->createTopic($moduleOne, 'Topic One');
        $quiz = $this->createRequiredModuleQuiz($lesson, $moduleOne);
        $moduleTwo = $this->createModule($lesson, 'Module Two', 2);
        $topicTwo = $this->createTopic($moduleTwo, 'Topic Two');

        $this->enroll($user, $lesson);
        $this->completeTopic($user, $lesson, $topicOne);

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

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertOk();
        $response->assertViewHas(
            'currentTopic',
            fn ($currentTopic) => $currentTopic?->id === $topicTwo->id
        );
    }

    public function test_student_can_move_between_topics_inside_current_unlocked_module(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $moduleOne = $this->createModule($lesson, 'Module One', 1);
        $this->createTopic($moduleOne, 'Topic One', 1);
        $topicTwo = $this->createTopic($moduleOne, 'Topic Two', 2);

        $this->enroll($user, $lesson);

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertOk();
        $response->assertViewHas(
            'currentTopic',
            fn ($currentTopic) => $currentTopic?->id === $topicTwo->id
        );
    }
}
