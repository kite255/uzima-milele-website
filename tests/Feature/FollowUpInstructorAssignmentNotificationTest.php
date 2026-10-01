<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use App\Notifications\FollowUpInstructorAssignedToStudentNotification;
use App\Notifications\NewStudentAssignedToInstructorNotification;
use App\Services\FollowUpInstructorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FollowUpInstructorAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_notifies_both_student_and_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $lesson = Lesson::query()->create([
            'title' => 'Bible Study Course',
            'slug' => 'bible-study-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        app(FollowUpInstructorAssignmentService::class)->assign($enrollment);

        Notification::assertSentTo(
            $student,
            FollowUpInstructorAssignedToStudentNotification::class,
            fn ($notification) =>
                $notification->enrollment->is($enrollment)
                && $notification->instructor->is($instructor)
        );

        Notification::assertSentTo(
            $instructor,
            NewStudentAssignedToInstructorNotification::class,
            fn ($notification) =>
                $notification->enrollment->is($enrollment)
                && $notification->student->is($student)
        );
    }

    public function test_reassigning_an_already_assigned_enrollment_does_not_send_duplicates(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $lesson = Lesson::query()->create([
            'title' => 'Bible Study Course',
            'slug' => 'bible-study-course-duplicate',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $service = app(FollowUpInstructorAssignmentService::class);

        $first = $service->assign($enrollment);
        $second = $service->assign($enrollment->fresh());

        $this->assertTrue($first->is($instructor));
        $this->assertTrue($second->is($instructor));

        Notification::assertSentToTimes(
            $student,
            FollowUpInstructorAssignedToStudentNotification::class,
            1
        );

        Notification::assertSentToTimes(
            $instructor,
            NewStudentAssignedToInstructorNotification::class,
            1
        );
    }
}
