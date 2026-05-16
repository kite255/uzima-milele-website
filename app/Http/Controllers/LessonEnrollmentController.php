<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Notifications\LessonEnrolledNotification;
use Illuminate\Http\Request;

class LessonEnrollmentController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('login', ['redirect' => route('lessons.show', $lesson->slug)])
                ->with('error', 'Tafadhali ingia kwanza ili kujiunga na somo.');
        }

        if (! $lesson->is_published) {
            abort(404);
        }

        $data = $request->validate([
            'study_pace' => ['nullable', 'in:relaxed,regular,intensive,custom'],
            'study_hours_per_week' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Decide the student's study pace
        |--------------------------------------------------------------------------
        | Priority:
        | 1. Student selected pace
        | 2. Course owner/admin recommended pace
        | 3. Regular pace
        */
        $studyPace = $data['study_pace']
            ?? $lesson->recommended_study_pace
            ?? 'regular';

        /*
        |--------------------------------------------------------------------------
        | Calculate weekly hours and target completion date
        |--------------------------------------------------------------------------
        | This uses the course owner's allocated time:
        | estimated_duration_minutes, min_completion_days,
        | max_completion_days, and course_deadline.
        */
        $customHours = $data['study_hours_per_week'] ?? null;

        $hoursPerWeek = $lesson->getPaceHours(
            pace: $studyPace,
            customHours: $customHours
        );

        $targetCompletionDate = $lesson->calculateTargetCompletionDate(
            pace: $studyPace,
            customHours: $customHours
        );

        /*
        |--------------------------------------------------------------------------
        | Find or create enrollment
        |--------------------------------------------------------------------------
        */
        $enrollment = LessonEnrollment::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $wasNewEnrollment = ! $enrollment->exists;

        /*
        |--------------------------------------------------------------------------
        | Prevent schedule changes if admin disabled reset
        |--------------------------------------------------------------------------
        | Existing students should not change schedule unless allowed.
        | But if the existing enrollment has no schedule yet, we still fill it.
        */
        $hasExistingSchedule = $enrollment->exists
            && filled($enrollment->study_pace)
            && filled($enrollment->target_completion_date);

        if (! $wasNewEnrollment && $hasExistingSchedule && ! $lesson->allow_schedule_reset) {
            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with('info', 'Tayari umejiunga na somo hili. Kubadilisha ratiba hakujaruhusiwa kwa somo hili.');
        }

        if ($wasNewEnrollment || ! $enrollment->enrolled_at) {
            $enrollment->enrolled_at = now();
        }

        /*
        |--------------------------------------------------------------------------
        | Save or update student's learning schedule
        |--------------------------------------------------------------------------
        */
        $enrollment->forceFill([
            'study_pace' => $studyPace,
            'study_hours_per_week' => $hoursPerWeek,
            'target_completion_date' => $targetCompletionDate,
            'schedule_started_at' => $enrollment->schedule_started_at ?: now(),
            'schedule_updated_at' => now(),
        ])->save();

        if ($wasNewEnrollment) {
            $user->notify(new LessonEnrolledNotification($lesson));

            return redirect()
                ->route('lessons.learn', $lesson->slug)
                ->with('success', 'Umejiunga na somo kikamilifu na ratiba yako ya kujifunza imeandaliwa.');
        }

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with('info', 'Ratiba yako ya kujifunza imesasishwa.');
    }
}