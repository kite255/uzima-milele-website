<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;

class LessonQuestionRecipients
{
    public function followUp(?LessonEnrollment $enrollment): ?User
    {
        $instructor = $enrollment?->followUpInstructor;

        return $instructor?->role === 'instructor' ? $instructor : null;
    }

    public function courseInstructor(?Lesson $lesson): ?User
    {
        if (! $lesson) {
            return null;
        }

        $lead = $lesson->leadInstructor;
        if ($lead?->role === 'instructor') {
            return $lead;
        }

        // The instructor inbox only grants legacy access if no lead or follow-up team is configured.
        $legacy = $lesson->instructor;
        if ($lesson->lead_instructor_id === null
            && $legacy?->role === 'instructor'
            && ! $lesson->followUpInstructors()->exists()) {
            return $legacy;
        }

        return null;
    }
}
