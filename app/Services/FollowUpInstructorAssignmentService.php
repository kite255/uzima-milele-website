<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Support\Collection;

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
            $instructors->push(
                $lesson->leadInstructor
            );
        }

        return $instructors
            ->unique('id')
            ->values();
    }

    public function assign(
        LessonEnrollment $enrollment
    ): ?User {
        $enrollment->loadMissing('lesson');

        $lesson = $enrollment->lesson;

        if (! $lesson) {
            return null;
        }

        $eligibleInstructors = $this
            ->eligibleInstructors($lesson);

        if ($eligibleInstructors->isEmpty()) {
            return null;
        }

        $instructorIds = $eligibleInstructors
            ->pluck('id');

        $workloads = LessonEnrollment::query()
            ->whereIn(
                'follow_up_instructor_id',
                $instructorIds
            )
            ->get()
            ->filter(
                fn (LessonEnrollment $item) =>
                    ! $item->is_completed
            )
            ->groupBy('follow_up_instructor_id')
            ->map(
                fn (Collection $items) =>
                    $items->count()
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

        $enrollment->forceFill([
            'follow_up_instructor_id' =>
                $selectedInstructor->id,

            'instructor_assigned_at' =>
                now(),
        ])->save();

        return $selectedInstructor;
    }
}
