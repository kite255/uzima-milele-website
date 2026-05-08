<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonTopicController extends Controller
{
    public function show(Lesson $lesson, LessonTopic $topic): View
    {
        $user = auth()->user();

        $isEnrolled = $user->lessonEnrollments()
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Hujaruhusiwa kusoma somo hili.');
        }

        if (! $lesson->is_published || ! $topic->is_published) {
            abort(404);
        }

        $topic->load(['module.lesson']);

        if ((int) $topic->module->lesson_id !== (int) $lesson->id) {
            abort(404);
        }

        $allTopics = $lesson->modules()
            ->where('is_published', true)
            ->with([
                'topics' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),
            ])
            ->orderBy('order')
            ->get()
            ->flatMap(fn ($module) => $module->topics)
            ->values();

        $currentIndex = $allTopics->search(fn ($item) => $item->id === $topic->id);

        $previousTopic = $currentIndex > 0 ? $allTopics[$currentIndex - 1] : null;

        $nextTopic = $currentIndex !== false && $currentIndex < $allTopics->count() - 1
            ? $allTopics[$currentIndex + 1]
            : null;

        $isCompleted = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('lesson_topic_id', $topic->id)
            ->exists();

        return view('student.topic-show', compact(
            'lesson',
            'topic',
            'previousTopic',
            'nextTopic',
            'isCompleted'
        ));
    }

    public function complete(Lesson $lesson, LessonTopic $topic): RedirectResponse
    {
        $user = auth()->user();

        $isEnrolled = $user->lessonEnrollments()
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Hujaruhusiwa kusoma somo hili.');
        }

        $topic->load('module');

        if ((int) $topic->module->lesson_id !== (int) $lesson->id) {
            abort(404);
        }

        LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'lesson_topic_id' => $topic->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        $allTopics = $lesson->modules()
            ->where('is_published', true)
            ->with([
                'topics' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),
            ])
            ->orderBy('order')
            ->get()
            ->flatMap(fn ($module) => $module->topics)
            ->values();

        $currentIndex = $allTopics->search(fn ($item) => $item->id === $topic->id);

        $nextTopic = $currentIndex !== false && $currentIndex < $allTopics->count() - 1
            ? $allTopics[$currentIndex + 1]
            : null;

        if ($nextTopic) {
            return redirect()
                ->route('lessons.learn', [
                    'lesson' => $lesson->slug,
                    'topic' => $nextTopic->id,
                ])
                ->with('success', 'Umehitimisha mada hii. Endelea na mada inayofuata.');
        }

        return redirect()
            ->route('lessons.learn', $lesson->slug)
            ->with('success', 'Hongera! Umemaliza mada zote za somo hili.');
    }
}