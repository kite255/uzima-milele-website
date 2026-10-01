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
use InvalidArgumentException;
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

    public function test_admin_can_manually_assign_an_eligible_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $lesson = Lesson::query()->create([
            'title' => 'Manual Assignment Course',
            'slug' => 'manual-assignment-course',
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

        $assigned = app(FollowUpInstructorAssignmentService::class)
            ->assignManually($enrollment, $instructor);

        $this->assertTrue($assigned->is($instructor));
        $this->assertSame(
            $instructor->id,
            $enrollment->fresh()->follow_up_instructor_id
        );

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

    public function test_manual_assignment_rejects_an_ineligible_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $eligibleInstructor = User::factory()->create(['role' => 'instructor']);
        $ineligibleInstructor = User::factory()->create(['role' => 'instructor']);

        $lesson = Lesson::query()->create([
            'title' => 'Protected Assignment Course',
            'slug' => 'protected-assignment-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($eligibleInstructor->id);

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(FollowUpInstructorAssignmentService::class)
            ->assignManually($enrollment, $ineligibleInstructor);
    }

    public function test_forced_automatic_assignment_uses_the_least_loaded_eligible_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $busyInstructor = User::factory()->create(['role' => 'instructor']);
        $availableInstructor = User::factory()->create(['role' => 'instructor']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Automatic Assignment Course',
            'slug' => 'automatic-assignment-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach([
            $busyInstructor->id,
            $availableInstructor->id,
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $otherStudent->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $busyInstructor->id,
            'instructor_assigned_at' => now(),
            'enrolled_at' => now(),
        ]);

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $busyInstructor->id,
            'instructor_assigned_at' => now()->subDay(),
            'enrolled_at' => now(),
        ]);

        $assigned = app(FollowUpInstructorAssignmentService::class)
            ->assignAutomatically($enrollment, reassign: true);

        $this->assertTrue($assigned->is($availableInstructor));
        $this->assertSame(
            $availableInstructor->id,
            $enrollment->fresh()->follow_up_instructor_id
        );
    }

    public function test_admin_can_assign_multiple_students_to_one_instructor(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $studentOne = User::factory()->create(['role' => 'student']);
        $studentTwo = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Bulk Manual Assignment Course',
            'slug' => 'bulk-manual-assignment-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $enrollmentOne = LessonEnrollment::query()->create([
            'user_id' => $studentOne->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $enrollmentTwo = LessonEnrollment::query()->create([
            'user_id' => $studentTwo->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $changed = app(FollowUpInstructorAssignmentService::class)
            ->assignManyManually(
                collect([$enrollmentOne, $enrollmentTwo]),
                $instructor
            );

        $this->assertSame(2, $changed);
        $this->assertSame($instructor->id, $enrollmentOne->fresh()->follow_up_instructor_id);
        $this->assertSame($instructor->id, $enrollmentTwo->fresh()->follow_up_instructor_id);

        Notification::assertSentToTimes(
            $studentOne,
            FollowUpInstructorAssignedToStudentNotification::class,
            1
        );
        Notification::assertSentToTimes(
            $studentTwo,
            FollowUpInstructorAssignedToStudentNotification::class,
            1
        );
        Notification::assertSentToTimes(
            $instructor,
            NewStudentAssignedToInstructorNotification::class,
            2
        );
    }

    public function test_bulk_manual_assignment_skips_students_already_assigned_to_same_instructor(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $studentOne = User::factory()->create(['role' => 'student']);
        $studentTwo = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Bulk Duplicate Assignment Course',
            'slug' => 'bulk-duplicate-assignment-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $alreadyAssigned = LessonEnrollment::query()->create([
            'user_id' => $studentOne->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
            'instructor_assigned_at' => now()->subDay(),
            'enrolled_at' => now(),
        ]);

        $unassigned = LessonEnrollment::query()->create([
            'user_id' => $studentTwo->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $changed = app(FollowUpInstructorAssignmentService::class)
            ->assignManyManually(
                collect([$alreadyAssigned, $unassigned]),
                $instructor
            );

        $this->assertSame(1, $changed);

        Notification::assertNotSentTo(
            $studentOne,
            FollowUpInstructorAssignedToStudentNotification::class
        );
        Notification::assertSentToTimes(
            $studentTwo,
            FollowUpInstructorAssignedToStudentNotification::class,
            1
        );
        Notification::assertSentToTimes(
            $instructor,
            NewStudentAssignedToInstructorNotification::class,
            1
        );
    }

    public function test_bulk_manual_assignment_rejects_enrollment_when_instructor_is_not_eligible_for_its_lesson(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $studentOne = User::factory()->create(['role' => 'student']);
        $studentTwo = User::factory()->create(['role' => 'student']);

        $eligibleLesson = Lesson::query()->create([
            'title' => 'Eligible Bulk Course',
            'slug' => 'eligible-bulk-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $otherLesson = Lesson::query()->create([
            'title' => 'Other Bulk Course',
            'slug' => 'other-bulk-course',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $eligibleLesson->followUpInstructors()->attach($instructor->id);

        $eligibleEnrollment = LessonEnrollment::query()->create([
            'user_id' => $studentOne->id,
            'lesson_id' => $eligibleLesson->id,
            'enrolled_at' => now(),
        ]);

        $ineligibleEnrollment = LessonEnrollment::query()->create([
            'user_id' => $studentTwo->id,
            'lesson_id' => $otherLesson->id,
            'enrolled_at' => now(),
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(FollowUpInstructorAssignmentService::class)
            ->assignManyManually(
                collect([$eligibleEnrollment, $ineligibleEnrollment]),
                $instructor
            );
    }
}
