<?php

namespace App\Http\Controllers;

use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstructorStudentController extends Controller
{
    public function show(
        Request $request,
        LessonEnrollment $enrollment
    ): View {
        $this->authorizeEnrollmentAccess(
            $request->user(),
            $enrollment
        );

        $enrollment->load([
            'user',
            'lesson.leadInstructor',
            'followUpInstructor',
            'followUps.instructor',
        ]);

        return view(
            'instructor.students.show',
            compact('enrollment')
        );
    }

    public function storeFollowUp(
        Request $request,
        LessonEnrollment $enrollment
    ): RedirectResponse {
        $user = $request->user();

        $this->authorizeEnrollmentAccess(
            $user,
            $enrollment
        );

        $validated = $request->validate([
            'contact_method' => [
                'required',
                Rule::in([
                    'phone',
                    'whatsapp',
                    'sms',
                    'email',
                    'in_person',
                    'other',
                ]),
            ],

            'outcome' => [
                'required',
                Rule::in([
                    'reached',
                    'no_answer',
                    'needs_support',
                    'follow_up_required',
                ]),
            ],

            'follow_up_status' => [
                'required',
                Rule::in([
                    LessonEnrollment::FOLLOW_UP_NOT_CONTACTED,
                    LessonEnrollment::FOLLOW_UP_CONTACTED,
                    LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP,
                    LessonEnrollment::FOLLOW_UP_DOING_WELL,
                    LessonEnrollment::FOLLOW_UP_COMPLETED,
                ]),
            ],

            'note' => [
                'required',
                'string',
                'max:5000',
            ],

            'next_follow_up_at' => [
                'nullable',
                'date',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $user,
            $enrollment
        ): void {
            $enrollment->followUps()->create([
                'instructor_id' => $user->id,
                'contact_method' => $validated['contact_method'],
                'outcome' => $validated['outcome'],
                'note' => $validated['note'],
                'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            ]);

            $enrollment->update([
                'follow_up_status' => $validated['follow_up_status'],
                'last_follow_up_at' => now(),
                'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            ]);
        });

        return redirect()
            ->route(
                'instructor.students.show',
                $enrollment
            )
            ->with(
                'success',
                'Follow-up recorded successfully.'
            );
    }

    private function authorizeEnrollmentAccess(
        ?User $user,
        LessonEnrollment $enrollment
    ): void {
        abort_unless(
            $user
            && in_array(
                $user->role,
                ['admin', 'instructor'],
                true
            ),
            403
        );

        if ($user->role === 'admin') {
            return;
        }

        $enrollment->loadMissing('lesson');

        $isAssignedFollowUpInstructor =
            (int) $enrollment->follow_up_instructor_id
            === (int) $user->id;

        $isLeadInstructor =
            (int) $enrollment->lesson?->lead_instructor_id
            === (int) $user->id;

        $isLegacyInstructor =
            ! $enrollment->lesson?->lead_instructor_id
            && (int) $enrollment->lesson?->instructor_id
            === (int) $user->id;

        abort_unless(
            $isAssignedFollowUpInstructor
            || $isLeadInstructor
            || $isLegacyInstructor,
            403
        );
    }
}