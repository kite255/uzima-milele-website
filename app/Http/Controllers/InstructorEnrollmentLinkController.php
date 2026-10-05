<?php

namespace App\Http\Controllers;

use App\Models\InstructorEnrollmentLink;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Services\InstructorEnrollmentReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstructorEnrollmentLinkController extends Controller
{
    public function index(
        InstructorEnrollmentReferralService $referrals
    ): View {
        $instructor = auth()->user();

        abort_unless($instructor?->role === 'instructor', 403);

        $lessons = Lesson::query()
            ->where('is_published', true)
            ->with([
                'leadInstructor',
                'followUpInstructors',
            ])
            ->orderBy('title')
            ->get()
            ->filter(
                fn (Lesson $lesson) => $referrals->isEligible(
                    $lesson,
                    $instructor
                )
            )
            ->map(function (Lesson $lesson) use ($referrals, $instructor) {
                $lesson->enrollment_link = $referrals->shortUrl(
                    $lesson,
                    $instructor
                );

                return $lesson;
            })
            ->values();

        return view(
            'instructor.enrollment-links',
            compact('lessons')
        );
    }

    public function join(
        string $code,
        InstructorEnrollmentReferralService $referrals
    ): View|RedirectResponse {
        $link = InstructorEnrollmentLink::query()
            ->with([
                'lesson',
                'instructor',
            ])
            ->where('code', strtoupper($code))
            ->firstOrFail();

        $lesson = $link->lesson;
        $instructor = $link->instructor;

        abort_if(! $lesson || ! $lesson->is_published, 404);
        abort_if(! $instructor || $instructor->role !== 'instructor', 404);
        abort_unless($referrals->isEligible($lesson, $instructor), 404);

        if (auth()->check()) {
            $existingEnrollment = LessonEnrollment::query()
                ->where('user_id', auth()->id())
                ->where('lesson_id', $lesson->id)
                ->first();

            if ($existingEnrollment) {
                $referrals->forget($lesson);

                return redirect()
                    ->route('lessons.learn', $lesson->slug)
                    ->with(
                        'success',
                        'Tayari umejiunga na somo hili. Utaendelea na mwalimu aliyepangiwa kwenye usajili wako wa sasa.'
                    );
            }
        }

        $referrals->remember($lesson, $instructor);

        return view(
            'instructor.enrollment-link-join',
            compact('lesson', 'instructor')
        );
    }
}
