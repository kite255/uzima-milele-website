<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

        $joinResponse = $this
            ->actingAs($student)
            ->get(
                '/lessons/' . $lesson->slug . '/join/' . $instructor->id
            );

        $joinResponse->assertRedirect(
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
}
