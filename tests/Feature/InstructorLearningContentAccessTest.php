<?php

namespace Tests\Feature;

use App\Filament\Resources\LessonTopicResource;
use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\QuizResource;
use App\Models\Lesson;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorLearningContentAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createInstructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    private function createLesson(
        string $title,
        array $attributes = []
    ): Lesson {
        return Lesson::query()->create(array_merge([
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'description' => 'Content access test.',
            'is_published' => true,
        ], $attributes));
    }

    private function createModule(
        Lesson $lesson,
        string $title
    ): Module {
        return Module::query()->create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'description' => 'Test module.',
            'order' => 1,
            'is_published' => true,
        ]);
    }

    private function createTopic(
        Module $module,
        string $title
    ): LessonTopic {
        return LessonTopic::query()->create([
            'module_id' => $module->id,
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'content' => 'Test topic content.',
            'order' => 1,
            'is_published' => true,
        ]);
    }

    private function createFinalQuiz(
        Lesson $lesson,
        string $title
    ): Quiz {
        return Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'quiz_type' => 'kujipima',
            'pass_mark' => 70,
            'is_required' => false,
            'is_published' => true,
        ]);
    }

    public function test_lead_instructor_can_access_course_content(): void
    {
        $lead = $this->createInstructor('Lead');

        $lesson = $this->createLesson(
            'Lead Course',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $module = $this->createModule(
            $lesson,
            'Lead Module'
        );

        $topic = $this->createTopic(
            $module,
            'Lead Topic'
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Lead Quiz'
        );

        $this->actingAs($lead);

        $this->assertTrue(
            ModuleResource::getEloquentQuery()
                ->whereKey($module->id)
                ->exists()
        );

        $this->assertTrue(
            LessonTopicResource::getEloquentQuery()
                ->whereKey($topic->id)
                ->exists()
        );

        $this->assertTrue(
            QuizResource::getEloquentQuery()
                ->whereKey($quiz->id)
                ->exists()
        );
    }

    public function test_follow_up_instructor_can_access_course_content(): void
    {
        $instructor = $this->createInstructor(
            'Follow Up Instructor'
        );

        $lesson = $this->createLesson(
            'Follow Up Course'
        );

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $module = $this->createModule(
            $lesson,
            'Follow Up Module'
        );

        $topic = $this->createTopic(
            $module,
            'Follow Up Topic'
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Follow Up Quiz'
        );

        $this->actingAs($instructor);

        $this->assertTrue(
            ModuleResource::getEloquentQuery()
                ->whereKey($module->id)
                ->exists()
        );

        $this->assertTrue(
            LessonTopicResource::getEloquentQuery()
                ->whereKey($topic->id)
                ->exists()
        );

        $this->assertTrue(
            QuizResource::getEloquentQuery()
                ->whereKey($quiz->id)
                ->exists()
        );
    }

    public function test_instructor_cannot_access_unrelated_course_content(): void
    {
        $instructor = $this->createInstructor(
            'Other Instructor'
        );

        $lesson = $this->createLesson(
            'Unrelated Course'
        );

        $module = $this->createModule(
            $lesson,
            'Unrelated Module'
        );

        $topic = $this->createTopic(
            $module,
            'Unrelated Topic'
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Unrelated Quiz'
        );

        $this->actingAs($instructor);

        $this->assertFalse(
            ModuleResource::getEloquentQuery()
                ->whereKey($module->id)
                ->exists()
        );

        $this->assertFalse(
            LessonTopicResource::getEloquentQuery()
                ->whereKey($topic->id)
                ->exists()
        );

        $this->assertFalse(
            QuizResource::getEloquentQuery()
                ->whereKey($quiz->id)
                ->exists()
        );
    }

    public function test_legacy_instructor_content_access_still_works(): void
    {
        $instructor = $this->createInstructor(
            'Legacy Instructor'
        );

        $lesson = $this->createLesson(
            'Legacy Course',
            [
                'instructor_id' => $instructor->id,
            ]
        );

        $module = $this->createModule(
            $lesson,
            'Legacy Module'
        );

        $topic = $this->createTopic(
            $module,
            'Legacy Topic'
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Legacy Quiz'
        );

        $this->actingAs($instructor);

        $this->assertTrue(
            ModuleResource::getEloquentQuery()
                ->whereKey($module->id)
                ->exists()
        );

        $this->assertTrue(
            LessonTopicResource::getEloquentQuery()
                ->whereKey($topic->id)
                ->exists()
        );

        $this->assertTrue(
            QuizResource::getEloquentQuery()
                ->whereKey($quiz->id)
                ->exists()
        );
    }
}