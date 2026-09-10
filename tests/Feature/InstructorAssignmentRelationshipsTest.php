<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorAssignmentRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    private function createLesson(array $attributes = []): Lesson
    {
        return Lesson::query()->create(array_merge([
            'title' => 'Test Lesson',
            'slug' => 'test-lesson-' . uniqid(),
            'description' => 'Test lesson description',
            'is_published' => true,
        ], $attributes));
    }

    public function test_lesson_has_a_lead_instructor_relationship(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->createLesson();

        $lesson->forceFill([
            'lead_instructor_id' => $instructor->id,
        ])->save();

        $lesson->refresh();

        $this->assertTrue(
            $lesson->leadInstructor->is($instructor)
        );
    }

    public function test_lesson_can_have_multiple_follow_up_instructors(): void
    {
        $lesson = $this->createLesson();

        $firstInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $secondInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson->followUpInstructors()->attach([
            $firstInstructor->id,
            $secondInstructor->id,
        ]);

        $lesson->refresh();

        $this->assertCount(
            2,
            $lesson->followUpInstructors
        );

        $this->assertTrue(
            $lesson->followUpInstructors->contains($firstInstructor)
        );

        $this->assertTrue(
            $lesson->followUpInstructors->contains($secondInstructor)
        );
    }

    public function test_enrollment_has_an_assigned_follow_up_instructor(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->createLesson();

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $enrollment->forceFill([
            'follow_up_instructor_id' => $instructor->id,
            'instructor_assigned_at' => now(),
        ])->save();

        $enrollment->refresh();

        $this->assertTrue(
            $enrollment->followUpInstructor->is($instructor)
        );
    }

    public function test_user_has_led_lessons_relationship(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->createLesson();

        $lesson->forceFill([
            'lead_instructor_id' => $instructor->id,
        ])->save();

        $instructor->refresh();

        $this->assertTrue(
            $instructor->ledLessons->contains($lesson)
        );
    }

    public function test_user_has_follow_up_lessons_relationship(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->createLesson();

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $instructor->refresh();

        $this->assertTrue(
            $instructor->followUpLessons->contains($lesson)
        );
    }

    public function test_user_has_assigned_lesson_enrollments_relationship(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->createLesson();

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $enrollment->forceFill([
            'follow_up_instructor_id' => $instructor->id,
            'instructor_assigned_at' => now(),
        ])->save();

        $instructor->refresh();

        $this->assertTrue(
            $instructor
                ->assignedLessonEnrollments
                ->contains($enrollment)
        );
    }
}