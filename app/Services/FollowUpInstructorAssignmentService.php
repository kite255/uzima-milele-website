<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use App\Notifications\FollowUpInstructorAssignedToStudentNotification;
use App\Notifications\NewStudentAssignedToInstructorNotification;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FollowUpInstructorAssignmentService
{
    public function eligibleInstructors(Lesson $lesson): Collection
    {
        $instructors = $lesson
            ->followUpInstructors()
            ->where('users.role', 'instructor')
            ->get();

        if (
            $lesson->lead_can_receive_students
            && $lesson->leadInstructor
            && $lesson->leadInstructor->role === 'instructor'
        ) {
            $instructors->push($lesson->leadInstructor);
        }

        return $instructors
            ->unique('id')
            ->values();
    }

    public function assign(LessonEnrollment $enrollment): ?User
    {
        return $this->assignAutomatically($enrollment);
    }

    public function assignAutomatically(
        LessonEnrollment $enrollment,
        bool $reassign = false
    ): ?User {
        $enrollment->loadMissing([
            'lesson',
            'user',
            'followUpInstructor',
        ]);

        if ($enrollment->followUpInstructor && ! $reassign) {
            return $enrollment->followUpInstructor;
        }

        $lesson = $enrollment->lesson;

        if (! $lesson) {
            return null;
        }

        $eligibleInstructors = $this->eligibleInstructors($lesson);

        if ($eligibleInstructors->isEmpty()) {
            return null;
        }

        $instructorIds = $eligibleInstructors->pluck('id');

        $workloads = LessonEnrollment::query()
            ->whereKeyNot($enrollment->id)
            ->whereIn('follow_up_instructor_id', $instructorIds)
            ->get()
            ->filter(
                fn (LessonEnrollment $item) => ! $item->is_completed
            )
            ->groupBy('follow_up_instructor_id')
            ->map(
                fn (Collection $items) => $items->count()
            );

        $selectedInstructor = $eligibleInstructors
            ->sortBy(function (User $instructor) use ($workloads) {
                return [
                    $workloads->get($instructor->id, 0),
                    $instructor->id,
                ];
            })
            ->first();

        if (! $selectedInstructor) {
            return null;
        }

        return $this->persistAssignment(
            $enrollment,
            $selectedInstructor
        );
    }

    public function assignManually(
        LessonEnrollment $enrollment,
        User $instructor
    ): User {
        $enrollment->loadMissing([
            'lesson',
            'user',
            'followUpInstructor',
        ]);

        $lesson = $enrollment->lesson;

        if (! $lesson) {
            throw new InvalidArgumentException(
                'Enrollment does not belong to a lesson.'
            );
        }

        $isEligible = $this
            ->eligibleInstructors($lesson)
            ->contains(
                fn (User $eligible) => $eligible->id === $instructor->id
            );

        if (! $isEligible) {
            throw new InvalidArgumentException(
                'The selected instructor is not eligible for this lesson.'
            );
        }

        return $this->persistAssignment(
            $enrollment,
            $instructor
        );
    }

    private function persistAssignment(
        LessonEnrollment $enrollment,
        User $instructor
    ): User {
        if ($enrollment->follow_up_instructor_id === $instructor->id) {
            return $instructor;
        }

        $enrollment->forceFill([
            'follow_up_instructor_id' => $instructor->id,
            'instructor_assigned_at' => now(),
        ])->save();

        $enrollment->setRelation(
            'followUpInstructor',
            $instructor
        );

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

        return $instructor;
    }
}
