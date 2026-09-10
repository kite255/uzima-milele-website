<?php

namespace Tests\Feature;

use App\Filament\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonResourceInstructorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createLesson(string $title): Lesson
    {
        return Lesson::query()->create([
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'description' => 'Test lesson.',
            'is_published' => true,
        ]);
    }

    private function createInstructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    public function test_lead_instructor_can_see_their_course(): void
    {
        $instructor = $this->createInstructor('Lead Instructor');

        $lesson = $this->createLesson('Lead Course');

        $lesson->forceFill([
            'lead_instructor_id' => $instructor->id,
        ])->save();

        $this->actingAs($instructor);

        $this->assertTrue(
            LessonResource::getEloquentQuery()
                ->whereKey($lesson->id)
                ->exists()
        );
    }

    public function test_follow_up_instructor_can_see_assigned_course(): void
    {
        $instructor = $this->createInstructor('Follow Up Instructor');

        $lesson = $this->createLesson('Follow Up Course');

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $this->actingAs($instructor);

        $this->assertTrue(
            LessonResource::getEloquentQuery()
                ->whereKey($lesson->id)
                ->exists()
        );
    }

    public function test_legacy_instructor_can_still_see_their_course(): void
    {
        $instructor = $this->createInstructor('Legacy Instructor');

        $lesson = $this->createLesson('Legacy Course');

        $lesson->forceFill([
            'instructor_id' => $instructor->id,
        ])->save();

        $this->actingAs($instructor);

        $this->assertTrue(
            LessonResource::getEloquentQuery()
                ->whereKey($lesson->id)
                ->exists()
        );
    }

    public function test_instructor_cannot_see_unrelated_course(): void
    {
        $instructor = $this->createInstructor('Other Instructor');

        $lesson = $this->createLesson('Unrelated Course');

        $this->actingAs($instructor);

        $this->assertFalse(
            LessonResource::getEloquentQuery()
                ->whereKey($lesson->id)
                ->exists()
        );
    }
}
