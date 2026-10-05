<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use App\Notifications\LessonEnrolledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LessonEnrollmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_enrollment_route_notifies_new_student(): void
    {
        Notification::fake();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Notification Test Course',
            'slug' => 'notification-test-course',
            'description' => 'Notification test course.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->post(
                route('lessons.enroll', $lesson),
                [
                    'study_pace' => Lesson::PACE_REGULAR,
                ]
            );

        $response->assertRedirect(
            route('lessons.learn', $lesson)
        );

        Notification::assertSentTo(
            $student,
            LessonEnrolledNotification::class
        );
    }
}
