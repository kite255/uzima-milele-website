<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Services\AskLessonQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LessonQuestionController extends Controller
{
    public function store(Request $request, Lesson $lesson, AskLessonQuestion $questions)
    {
        abort_if(! $lesson->is_published, 404);

        $user = auth()->user();

        $enrollment = LessonEnrollment::query()
            ->where('lesson_id', $lesson->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Tafadhali jiunge na somo hili kwanza kabla ya kuuliza swali.');
        }

        $validated = $request->validate([
            'question' => AskLessonQuestion::QUESTION_RULES,
            'lesson_topic_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $topic = null;
        if (isset($validated['lesson_topic_id'])) {
            $topic = $lesson->publishedTopics()
                ->where('modules.is_published', true)
                ->find($validated['lesson_topic_id']);
            if (! $topic) {
                throw ValidationException::withMessages([
                    'lesson_topic_id' => 'Mada hii haipatikani kwenye somo ulilochagua.',
                ]);
            }
        }

        $questions->submit($enrollment, $lesson, $validated['question'], $topic);

        return back()->with('success', 'Swali lako limetumwa kikamilifu.');
    }
}
