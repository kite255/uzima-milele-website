<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InstructorEnrollmentLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_joining_through_instructor_link_is_assigned_to_that_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Instructor Link Lesson',
            'slug' => 'instructor-link-lesson',
            'description' => 'Lesson used to test instructor enrollment links.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $joinUrl = URL::signedRoute(
            'lessons.instructor-join',
            [
                'lesson' => $lesson->slug,
                'instructor' => $instructor->id,
            ]
        );

        $this
            ->get($joinUrl)
            ->assertRedirect(
                route('lessons.show', ['lesson' => $lesson->slug])
            );

        $this
            ->actingAs($student)
            ->post(
                route('lessons.enroll', ['lesson' => $lesson->slug]),
                [
                    'study_pace' => Lesson::PACE_REGULAR,
                ]
            )
            ->assertRedirect(
                route('lessons.learn', ['lesson' => $lesson->slug])
            );

        $enrollment = LessonEnrollment::query()
            ->where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertSame(
            $instructor->id,
            $enrollment->follow_up_instructor_id
        );
    }

    public function test_ineligible_instructor_cannot_claim_student_with_signed_link(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Protected Instructor Link Lesson',
            'slug' => 'protected-instructor-link-lesson',
            'description' => 'Lesson used to test instructor eligibility.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);

        $joinUrl = URL::signedRoute(
            'lessons.instructor-join',
            [
                'lesson' => $lesson->slug,
                'instructor' => $instructor->id,
            ]
        );

        $this->get($joinUrl)->assertNotFound();
    }

    public function test_tampered_instructor_join_link_is_rejected(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Signed Instructor Link Lesson',
            'slug' => 'signed-instructor-link-lesson',
            'description' => 'Lesson used to test signed links.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach([
            $instructor->id,
            $otherInstructor->id,
        ]);

        $joinUrl = URL::signedRoute(
            'lessons.instructor-join',
            [
                'lesson' => $lesson->slug,
                'instructor' => $instructor->id,
            ]
        );

        $tamperedUrl = str_replace(
            '/join/' . $instructor->id,
            '/join/' . $otherInstructor->id,
            $joinUrl
        );

        $this->get($tamperedUrl)->assertForbidden();
    }
}
