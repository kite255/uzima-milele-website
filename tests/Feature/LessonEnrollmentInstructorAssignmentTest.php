<?php

namespace Tests\Feature;

use App\Http\Controllers\LessonEnrollmentController;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LessonEnrollmentInstructorAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function createLesson(array $attributes = []): Lesson
    {
        return Lesson::query()->create(array_merge([
            'title' => 'Assignment Test Course',
            'slug' => 'assignment-test-' . uniqid(),
            'description' => 'Assignment test course.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ], $attributes));
    }

    private function createInstructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    private function createStudent(): User
    {
        return User::factory()->create([
            'role' => 'student',
        ]);
    }

    public function test_create_for_lesson_automatically_assigns_follow_up_instructor(): void
    {
        $lesson = $this->createLesson();

        $instructor = $this->createInstructor('John');

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $student = $this->createStudent();

        $enrollment = LessonEnrollment::createForLesson(
            user: $student,
            lesson: $lesson,
            pace: Lesson::PACE_REGULAR
        );

        $enrollment->refresh();

        $this->assertSame(
            $instructor->id,
            $enrollment->follow_up_instructor_id
        );

        $this->assertNotNull(
            $enrollment->instructor_assigned_at
        );
    }

    public function test_controller_new_enrollment_automatically_assigns_follow_up_instructor(): void
    {
        $lesson = $this->createLesson();

        $instructor = $this->createInstructor('Mary');

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $student = $this->createStudent();

        $request = Request::create(
            '/lessons/' . $lesson->slug . '/enroll',
            'POST',
            [
                'study_pace' => Lesson::PACE_REGULAR,
            ]
        );

        $request->setUserResolver(
            fn () => $student
        );

        app(LessonEnrollmentController::class)
            ->store($request, $lesson);

        $enrollment = LessonEnrollment::query()
            ->where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertSame(
            $instructor->id,
            $enrollment->follow_up_instructor_id
        );

        $this->assertNotNull(
            $enrollment->instructor_assigned_at
        );
    }

    public function test_existing_enrollment_is_not_reassigned_when_schedule_is_updated(): void
    {
        $lesson = $this->createLesson([
            'allow_schedule_reset' => true,
        ]);

        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $student = $this->createStudent();

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $john->id,
            'instructor_assigned_at' => now()->subDay(),
            'enrolled_at' => now()->subDay(),
            'study_pace' => Lesson::PACE_REGULAR,
            'study_hours_per_week' => 3,
            'target_completion_date' => now()->addDays(7),
            'schedule_started_at' => now()->subDay(),
            'schedule_updated_at' => now()->subDay(),
        ]);

        $originalAssignedAt =
            $enrollment->instructor_assigned_at->copy();

        $request = Request::create(
            '/lessons/' . $lesson->slug . '/enroll',
            'POST',
            [
                'study_pace' => Lesson::PACE_INTENSIVE,
            ]
        );

        $request->setUserResolver(
            fn () => $student
        );

        app(LessonEnrollmentController::class)
            ->store($request, $lesson);

        $enrollment->refresh();

        $this->assertSame(
            $john->id,
            $enrollment->follow_up_instructor_id
        );

        $this->assertTrue(
            $enrollment
                ->instructor_assigned_at
                ->equalTo($originalAssignedAt)
        );
    }

    public function test_enrollment_still_succeeds_when_no_instructors_are_configured(): void
    {
        $lesson = $this->createLesson();

        $student = $this->createStudent();

        $enrollment = LessonEnrollment::createForLesson(
            user: $student,
            lesson: $lesson,
            pace: Lesson::PACE_REGULAR
        );

        $this->assertNotNull($enrollment->id);

        $this->assertNull(
            $enrollment->follow_up_instructor_id
        );
    }
}