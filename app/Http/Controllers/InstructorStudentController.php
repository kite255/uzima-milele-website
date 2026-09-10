<?php

namespace App\Http\Controllers;

use App\Models\LessonEnrollment;
use Illuminate\Http\Request;

class InstructorStudentController extends Controller
{
    public function show(Request $request, LessonEnrollment $enrollment)
    {
        $user = $request->user();

        abort_unless(
            $user && in_array($user->role, ['admin', 'instructor']),
            403
        );

        $enrollment->load([
            'user',
            'lesson.leadInstructor',
            'followUpInstructor',
            'followUps.instructor',
        ]);

        if ($user->role === 'instructor') {
            $isAssignedFollowUpInstructor =
                (int) $enrollment->follow_up_instructor_id === (int) $user->id;

            $isLeadInstructor =
                (int) $enrollment->lesson?->lead_instructor_id === (int) $user->id;

            $isLegacyInstructor =
                ! $enrollment->lesson?->lead_instructor_id
                && (int) $enrollment->lesson?->instructor_id === (int) $user->id;

            abort_unless(
                $isAssignedFollowUpInstructor
                || $isLeadInstructor
                || $isLegacyInstructor,
                403
            );
        }

        return view(
            'instructor.students.show',
            compact('enrollment')
        );
    }
}