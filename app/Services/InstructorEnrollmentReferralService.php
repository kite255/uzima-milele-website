<?php

namespace App\Services;

use App\Models\InstructorEnrollmentLink;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Str;

class InstructorEnrollmentReferralService
{
    private array $pendingAssignments = [];

    public function __construct(
        private readonly FollowUpInstructorAssignmentService $assignmentService
    ) {
    }

    public function isEligible(Lesson $lesson, User $instructor): bool
    {
        return $this->assignmentService
            ->eligibleInstructors($lesson)
            ->contains(
                fn (User $eligible) => $eligible->id === $instructor->id
            );
    }

    public function shortUrl(Lesson $lesson, User $instructor): ?string
    {
        if (! $lesson->is_published || ! $this->isEligible($lesson, $instructor)) {
            return null;
        }

        $link = InstructorEnrollmentLink::query()->firstOrCreate(
            [
                'lesson_id' => $lesson->id,
                'instructor_id' => $instructor->id,
            ],
            [
                'code' => $this->generateUniqueCode(),
            ]
        );

        return route('lessons.instructor-join', [
            'code' => $link->code,
        ]);
    }

    public function remember(Lesson $lesson, User $instructor): void
    {
        abort_unless($this->isEligible($lesson, $instructor), 404);

        session()->put(
            $this->sessionKey($lesson),
            $instructor->id
        );
    }

    public function forget(Lesson $lesson): void
    {
        session()->forget($this->sessionKey($lesson));
    }

    public function pullEligibleInstructor(Lesson $lesson): ?User
    {
        $instructorId = session()->pull($this->sessionKey($lesson));

        if (! $instructorId) {
            return null;
        }

        return $this->assignmentService
            ->eligibleInstructors($lesson)
            ->first(
                fn (User $instructor) => $instructor->id === (int) $instructorId
            );
    }

    public function markPendingAssignment(
        int $userId,
        int $lessonId,
        User $instructor
    ): void {
        $this->pendingAssignments[$this->pendingKey($userId, $lessonId)] = $instructor;
    }

    public function takePendingAssignment(
        int $userId,
        int $lessonId
    ): ?User {
        $key = $this->pendingKey($userId, $lessonId);
        $instructor = $this->pendingAssignments[$key] ?? null;

        unset($this->pendingAssignments[$key]);

        return $instructor;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (
            InstructorEnrollmentLink::query()
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }

    private function sessionKey(Lesson $lesson): string
    {
        return 'lesson_instructor_referrals.' . $lesson->id;
    }

    private function pendingKey(int $userId, int $lessonId): string
    {
        return $userId . ':' . $lessonId;
    }
}
