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

        $enrollment = LessonEnrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'enrolled_at' => now(),
            ]
        );

        if ($enrollment->wasRecentlyCreated) {
            $user->notify(new LessonEnrolledNotification($lesson));
        }

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with(
                'success',
                $enrollment->wasRecentlyCreated
                    ? 'Umejiunga na somo kikamilifu.'
                    : 'Tayari umejiunga na somo hili.'
            );
    }
}