<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\StudentFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorRecordFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_follow_up_instructor_can_record_follow_up(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $response = $this->actingAs($followUp)
            ->post(
                route('instructor.students.follow-ups.store', $enrollment),
                [
                    'contact_method' => 'whatsapp',
                    'outcome' => 'reached',
                    'follow_up_status' => LessonEnrollment::FOLLOW_UP_CONTACTED,
                    'note' => 'Student responded and is continuing with the lesson.',
                    'next_follow_up_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                ]
            );

        $response
            ->assertRedirect(
                route('instructor.students.show', $enrollment)
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_follow_ups', [
            'lesson_enrollment_id' => $enrollment->id,
            'instructor_id' => $followUp->id,
            'contact_method' => 'whatsapp',
            'outcome' => 'reached',
            'note' => 'Student responded and is continuing with the lesson.',
        ]);

        $enrollment->refresh();

        $this->assertSame(
            LessonEnrollment::FOLLOW_UP_CONTACTED,
            $enrollment->follow_up_status
        );

        $this->assertNotNull(
            $enrollment->last_follow_up_at
        );

        $this->assertNotNull(
            $enrollment->next_follow_up_at
        );
    }

    public function test_lead_instructor_can_record_follow_up_for_student_in_led_lesson(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $response = $this->actingAs($lead)
            ->post(
                route('instructor.students.follow-ups.store', $enrollment),
                [
                    'contact_method' => 'phone',
                    'outcome' => 'reached',
                    'follow_up_status' => LessonEnrollment::FOLLOW_UP_DOING_WELL,
                    'note' => 'Lead instructor checked on student progress.',
                    'next_follow_up_at' => null,
                ]
            );

        $response->assertRedirect(
            route('instructor.students.show', $enrollment)
        );

        $this->assertDatabaseHas('student_follow_ups', [
            'lesson_enrollment_id' => $enrollment->id,
            'instructor_id' => $lead->id,
            'contact_method' => 'phone',
            'outcome' => 'reached',
        ]);
    }

    public function test_unrelated_instructor_cannot_record_follow_up(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $unrelatedInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($unrelatedInstructor)
            ->post(
                route('instructor.students.follow-ups.store', $enrollment),
                [
                    'contact_method' => 'phone',
                    'outcome' => 'reached',
                    'follow_up_status' => LessonEnrollment::FOLLOW_UP_CONTACTED,
                    'note' => 'This should never be saved.',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseMissing('student_follow_ups', [
            'lesson_enrollment_id' => $enrollment->id,
            'instructor_id' => $unrelatedInstructor->id,
        ]);
    }

    public function test_follow_up_requires_valid_data(): void
    {
        [$lesson, $lead, $followUp, $student, $enrollment] =
            $this->createAssignedEnrollment();

        $response = $this->actingAs($followUp)
            ->from(route('instructor.students.show', $enrollment))
            ->post(
                route('instructor.students.follow-ups.store', $enrollment),
                [
                    'contact_method' => '',
                    'outcome' => '',
                    'follow_up_status' => 'invalid_status',
                    'note' => '',
                ]
            );

        $response
            ->assertRedirect(
                route('instructor.students.show', $enrollment)
            )
            ->assertSessionHasErrors([
                'contact_method',
                'outcome',
                'follow_up_status',
                'note',
            ]);

        $this->assertSame(
            0,
            StudentFollowUp::query()->count()
        );
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
            'title' => 'Follow-up Recording Test Lesson',
            'slug' => 'follow-up-recording-test-lesson',
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