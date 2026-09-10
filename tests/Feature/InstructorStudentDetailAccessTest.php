<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorStudentDetailAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_up_instructor_can_view_assigned_student(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $this->actingAs($followUp)
            ->get(route('instructor.students.show', $enrollment))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($lesson->title);
    }

    public function test_follow_up_instructor_cannot_view_another_instructors_student(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($otherInstructor)
            ->get(route('instructor.students.show', $enrollment))
            ->assertForbidden();
    }

    public function test_lead_instructor_can_view_any_student_in_led_lesson(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $this->actingAs($lead)
            ->get(route('instructor.students.show', $enrollment))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_unrelated_instructor_cannot_view_student(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $unrelatedInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($unrelatedInstructor)
            ->get(route('instructor.students.show', $enrollment))
            ->assertForbidden();
    }

    private function createAssignedEnrollment(): array
    {
        $lead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $followUp = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Instructor Detail Test Lesson',
            'slug' => 'instructor-detail-test-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach(
            $followUp->id
        );

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUp->id,
            'instructor_assigned_at' => now(),
            'enrolled_at' => now(),
        ]);

        return [
            $lesson,
            $lead,
            $followUp,
            $student,
            $enrollment,
        ];
    }
}