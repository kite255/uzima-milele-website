<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorFollowUpReminderDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_up_instructor_sees_only_their_due_follow_ups(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $dueStudent = User::factory()->create([
            'name' => 'Due Follow-up Student',
            'role' => 'student',
        ]);

        $futureStudent = User::factory()->create([
            'name' => 'Future Follow-up Student',
            'role' => 'student',
        ]);

        $otherStudent = User::factory()->create([
            'name' => 'Other Instructor Student',
            'role' => 'student',
        ]);

        $lesson = $this->createLesson(
            'Reminder Test Lesson',
            'reminder-test-lesson'
        );

        $lesson->followUpInstructors()->attach([
            $instructor->id,
            $otherInstructor->id,
        ]);

        $dueEnrollment = LessonEnrollment::query()->create([
            'user_id' => $dueStudent->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
            'follow_up_status' => LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP,
            'next_follow_up_at' => now()->subHour(),
            'enrolled_at' => now(),
        ]);

        $futureEnrollment = LessonEnrollment::query()->create([
            'user_id' => $futureStudent->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
            'follow_up_status' => LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP,
            'next_follow_up_at' => now()->addDays(3),
            'enrolled_at' => now(),
        ]);

        $otherEnrollment = LessonEnrollment::query()->create([
            'user_id' => $otherStudent->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $otherInstructor->id,
            'follow_up_status' => LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP,
            'next_follow_up_at' => now()->subHour(),
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($instructor)
            ->get(route('instructor.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Due Follow-ups')
            ->assertViewHas(
                'dueFollowUps',
                function ($dueFollowUps) use (
                    $dueEnrollment,
                    $futureEnrollment,
                    $otherEnrollment
                ) {
                    return $dueFollowUps->contains('id', $dueEnrollment->id)
                        && ! $dueFollowUps->contains('id', $futureEnrollment->id)
                        && ! $dueFollowUps->contains('id', $otherEnrollment->id);
                }
            );
    }

    public function test_lead_instructor_sees_due_follow_ups_for_students_in_led_lesson(): void
    {
        $lead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $followUp = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'name' => 'Lead Visible Due Student',
            'role' => 'student',
        ]);

        $lesson = $this->createLesson(
            'Lead Reminder Lesson',
            'lead-reminder-lesson',
            $lead->id
        );

        $lesson->followUpInstructors()->attach(
            $followUp->id
        );

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUp->id,
            'follow_up_status' => LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP,
            'next_follow_up_at' => now()->subMinutes(30),
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($lead)
            ->get(route('instructor.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Due Follow-ups')
            ->assertViewHas(
                'dueFollowUps',
                fn ($dueFollowUps) =>
                    $dueFollowUps->contains('id', $enrollment->id)
            );
    }

    private function createLesson(
        string $title,
        string $slug,
        ?int $leadInstructorId = null
    ): Lesson {
        return Lesson::query()->create([
            'title' => $title,
            'slug' => $slug,
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $leadInstructorId,
        ]);
    }
}