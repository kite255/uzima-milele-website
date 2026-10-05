<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Notifications\FollowUpInstructorAssignedToStudentNotification;
use App\Notifications\NewStudentAssignedToInstructorNotification;
use App\Services\InstructorEnrollmentReferralService;

class LessonEnrollmentObserver
{
    public function __construct(
        private readonly InstructorEnrollmentReferralService $referrals
    ) {
    }

    public function creating(LessonEnrollment $enrollment): void
    {
        if (! request()->hasSession()) {
            return;
        }

        if (! auth()->check() || auth()->id() !== (int) $enrollment->user_id) {
            return;
        }

        $lesson = Lesson::query()->find($enrollment->lesson_id);

        if (! $lesson) {
            return;
        }

        $instructor = $this->referrals->pullEligibleInstructor($lesson);

        if (! $instructor) {
            return;
        }

        $enrollment->follow_up_instructor_id = $instructor->id;
        $enrollment->instructor_assigned_at = now();

        $this->referrals->markPendingAssignment(
            (int) $enrollment->user_id,
            (int) $enrollment->lesson_id,
            $instructor
        );
    }

    public function created(LessonEnrollment $enrollment): void
    {
        $instructor = $this->referrals->takePendingAssignment(
            (int) $enrollment->user_id,
            (int) $enrollment->lesson_id
        );

        if (! $instructor) {
            return;
        }

        $enrollment->loadMissing([
            'user',
            'lesson',
        ]);

        if ($enrollment->user) {
            $enrollment->user->notify(
                new FollowUpInstructorAssignedToStudentNotification(
                    $enrollment,
                    $instructor
                )
            );
        }

        $instructor->notify(
            new NewStudentAssignedToInstructorNotification(
                $enrollment,
                $enrollment->user
            )
        );
    }
}
