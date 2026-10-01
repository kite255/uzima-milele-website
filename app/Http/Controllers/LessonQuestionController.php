<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use App\Notifications\LessonQuestionAskedNotification;
use Illuminate\Http\Request;

class LessonQuestionController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $enrollment = LessonEnrollment::query()
            ->with('followUpInstructor')
            ->where('lesson_id', $lesson->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Tafadhali jiunge na somo hili kwanza kabla ya kuuliza swali.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $question = LessonQuestion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'question' => $validated['question'],
            'status' => LessonQuestion::STATUS_PENDING,
            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
            'is_published' => true,
        ]);

        $question->load(['lesson', 'user']);
        $lesson->loadMissing(['leadInstructor', 'instructor']);

        $recipient = $enrollment->followUpInstructor
            ?: $lesson->leadInstructor
            ?: $lesson->instructor;

        if ($recipient) {
            $recipient->notify(new LessonQuestionAskedNotification($question));
        } else {
            User::query()
                ->where('role', 'admin')
                ->get()
                ->each(fn (User $admin) => $admin->notify(
                    new LessonQuestionAskedNotification($question)
                ));
        }

        return back()->with('success', 'Swali lako limetumwa kikamilifu.');
    }
}
