<?php

namespace Tests\Feature;

use App\Http\Controllers\InstructorDashboardController;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorDashboardAssignmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createInstructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    private function createStudent(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'student',
        ]);
    }

    private function createLesson(
        string $title,
        array $attributes = []
    ): Lesson {
        return Lesson::query()->create(array_merge([
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'description' => 'Dashboard test lesson.',
            'is_published' => true,
        ], $attributes));
    }

    private function createEnrollment(
        Lesson $lesson,
        User $student,
        ?User $instructor = null
    ): LessonEnrollment {
        return LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor?->id,
            'instructor_assigned_at' => $instructor ? now() : null,
            'enrolled_at' => now(),
        ]);
    }

    public function test_follow_up_instructor_dashboard_counts_only_assigned_students(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson('Follow-up Course');

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $this->createStudent('John Student'),
            $john
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Mary Student'),
            $mary
        );

        $this->actingAs($john);

        $view = app(
            InstructorDashboardController::class
        )->index();

        $data = $view->getData();

        $this->assertSame(
            1,
            $data['totalLessons']
        );

        $this->assertSame(
            1,
            $data['totalStudents']
        );

        $this->assertTrue(
            $data['lessons']->contains($lesson)
        );
    }

    public function test_lead_instructor_dashboard_counts_all_course_students(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson(
            'Lead Course',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Student One'),
            $john
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Student Two'),
            $mary
        );

        $this->actingAs($lead);

        $view = app(
            InstructorDashboardController::class
        )->index();

        $data = $view->getData();

        $this->assertSame(
            1,
            $data['totalLessons']
        );

        $this->assertSame(
            2,
            $data['totalStudents']
        );

        $this->assertTrue(
            $data['lessons']->contains($lesson)
        );
    }

    public function test_follow_up_instructor_does_not_see_unrelated_course(): void
    {
        $john = $this->createInstructor('John');

        $assignedLesson = $this->createLesson(
            'Assigned Course'
        );

        $unrelatedLesson = $this->createLesson(
            'Unrelated Course'
        );

        $assignedLesson
            ->followUpInstructors()
            ->attach($john->id);

        $this->actingAs($john);

        $view = app(
            InstructorDashboardController::class
        )->index();

        $lessons = $view
            ->getData()['lessons'];

        $this->assertTrue(
            $lessons->contains($assignedLesson)
        );

        $this->assertFalse(
            $lessons->contains($unrelatedLesson)
        );
    }

    public function test_legacy_instructor_course_remains_visible(): void
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

        $this->actingAs($instructor);

        $lessons = app(
            InstructorDashboardController::class
        )
            ->index()
            ->getData()['lessons'];

        $this->assertTrue(
            $lessons->contains($lesson)
        );
    }
}