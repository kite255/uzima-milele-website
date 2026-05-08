<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\LessonTopic;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function store(Request $request, LessonTopic $topic)
    {
        $user = $request->user();

        if (! $topic->is_published) {
            abort(404);
        }

        $topic->load('module.lesson');

        $lesson = $topic->module->lesson;

        $isEnrolled = $user->lessonEnrollments()
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo kwanza.');
        }

        LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_topic_id' => $topic->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with('success', 'Umehitimisha sehemu hii kikamilifu.');
    }
}