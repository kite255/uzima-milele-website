<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
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

        $isEnrolled = $lesson->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            return back()->with('error', 'Tafadhali jiunge na somo hili kwanza kabla ya kuuliza swali.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $question = LessonQuestion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'question' => $validated['question'],
            'is_published' => true,
        ]);

        $question->load(['lesson', 'user']);

        /*
        |--------------------------------------------------------------------------
        | Notify instructor if assigned, otherwise notify all admins
        |--------------------------------------------------------------------------
        */
        if ($lesson->instructor) {
            $lesson->instructor->notify(new LessonQuestionAskedNotification($question));
        } else {
            User::where('role', 'admin')
                ->get()
                ->each(fn ($admin) => $admin->notify(new LessonQuestionAskedNotification($question)));
        }

        return back()->with('success', 'Swali lako limetumwa kikamilifu.');
    }
}