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
        | Use student's selected pace, or admin recommended pace, or regular
        |--------------------------------------------------------------------------
        */
        $studyPace = $data['study_pace']
            ?? $lesson->recommended_study_pace
            ?? 'regular';

        $hoursPerWeek = match ($studyPace) {
            'relaxed' => 1,
            'regular' => 3,
            'intensive' => 5,
            'custom' => (int) ($data['study_hours_per_week'] ?? 3),
            default => 3,
        };

        /*
        |--------------------------------------------------------------------------
        | Calculate target completion date from estimated course duration
        |--------------------------------------------------------------------------
        */
        $estimatedMinutes = (int) ($lesson->estimated_duration_minutes ?? 60);
        $estimatedHours = max(1, ceil($estimatedMinutes / 60));

        $weeksNeeded = max(1, ceil($estimatedHours / $hoursPerWeek));
        $targetCompletionDate = now()->addWeeks($weeksNeeded);

        /*
        |--------------------------------------------------------------------------
        | Apply admin/instructor timeline rules
        |--------------------------------------------------------------------------
        | 1. Minimum completion days
        | 2. Maximum completion days
        | 3. Course deadline
        */
        if ($lesson->min_completion_days) {
            $minimumDate = now()->addDays((int) $lesson->min_completion_days);

            if ($targetCompletionDate->lessThan($minimumDate)) {
                $targetCompletionDate = $minimumDate;
            }
        }

        if ($lesson->max_completion_days) {
            $maximumDate = now()->addDays((int) $lesson->max_completion_days);

            if ($targetCompletionDate->greaterThan($maximumDate)) {
                $targetCompletionDate = $maximumDate;
            }
        }

        if ($lesson->course_deadline) {
            $deadlineDate = $lesson->course_deadline->copy()->endOfDay();

            if ($targetCompletionDate->greaterThan($deadlineDate)) {
                $targetCompletionDate = $deadlineDate;
            }
        }

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
        | If already enrolled and schedule reset is disabled
        |--------------------------------------------------------------------------
        | Existing students should not be able to change schedule unless allowed.
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

        if ($wasNewEnrollment) {
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