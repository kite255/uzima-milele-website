<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use App\Services\FollowUpInstructorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpInstructorAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createLesson(array $attributes = []): Lesson
    {
        return Lesson::query()->create(array_merge([
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'description' => 'Test course description',
            'is_published' => true,
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

    public function test_it_assigns_new_student_to_least_loaded_instructor(): void
    {
        $lesson = $this->createLesson();

        $kisija = $this->createInstructor('Kisija');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson->followUpInstructors()->attach([
            $kisija->id,
            $john->id,
            $mary->id,
        ]);

        // Kisija already has 2 students.
        $this->createEnrollment(
            $lesson,
            $this->createStudent(),
            $kisija
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent(),
            $kisija
        );

        // Mary already has 1 student.
        $this->createEnrollment(
            $lesson,
            $this->createStudent(),
            $mary
        );

        // John currently has 0.
        $newEnrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent()
        );

        $assigned = app(
            FollowUpInstructorAssignmentService::class
        )->assign($newEnrollment);

        $newEnrollment->refresh();

        $this->assertTrue($assigned->is($john));

        $this->assertSame(
            $john->id,
            $newEnrollment->follow_up_instructor_id
        );

        $this->assertNotNull(
            $newEnrollment->instructor_assigned_at
        );
    }

    public function test_lead_instructor_is_excluded_when_receive_students_is_disabled(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');

        $lesson = $this->createLesson([
            'lead_instructor_id' => $lead->id,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($john->id);

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent()
        );

        $assigned = app(
            FollowUpInstructorAssignmentService::class
        )->assign($enrollment);

        $this->assertTrue($assigned->is($john));

        $this->assertNotSame(
            $lead->id,
            $enrollment->fresh()->follow_up_instructor_id
        );
    }

    public function test_lead_instructor_can_receive_students_when_enabled(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');

        $lesson = $this->createLesson([
            'lead_instructor_id' => $lead->id,
            'lead_can_receive_students' => true,
        ]);

        $lesson->followUpInstructors()->attach($john->id);

        // Give John an existing student so lead has the lower workload.
        $this->createEnrollment(
            $lesson,
            $this->createStudent(),
            $john
        );

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent()
        );

        $assigned = app(
            FollowUpInstructorAssignmentService::class
        )->assign($enrollment);

        $this->assertTrue($assigned->is($lead));

        $this->assertSame(
            $lead->id,
            $enrollment->fresh()->follow_up_instructor_id
        );
    }

    public function test_it_leaves_student_unassigned_when_course_has_no_eligible_instructors(): void
    {
        $lesson = $this->createLesson();

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent()
        );

        $assigned = app(
            FollowUpInstructorAssignmentService::class
        )->assign($enrollment);

        $enrollment->refresh();

        $this->assertNull($assigned);
        $this->assertNull($enrollment->follow_up_instructor_id);
        $this->assertNull($enrollment->instructor_assigned_at);
    }

    public function test_only_users_with_instructor_role_are_eligible(): void
    {
        $lesson = $this->createLesson();

        $realInstructor = $this->createInstructor(
            'Real Instructor'
        );

        $admin = User::factory()->create([
            'name' => 'Administrator',
            'role' => 'admin',
        ]);

        $lesson->followUpInstructors()->attach([
            $realInstructor->id,
            $admin->id,
        ]);

        // Make the actual instructor look busier.
        $this->createEnrollment(
            $lesson,
            $this->createStudent(),
            $realInstructor
        );

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent()
        );

        $assigned = app(
            FollowUpInstructorAssignmentService::class
        )->assign($enrollment);

        // Admin must not be selected even though admin has lower workload.
        $this->assertTrue(
            $assigned->is($realInstructor)
        );
    }
}