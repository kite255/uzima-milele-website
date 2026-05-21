<?php

namespace App\Http\Controllers;

use App\Models\LessonQuestion;
use App\Notifications\LessonQuestionAnsweredNotification;
use Illuminate\Http\Request;

class InstructorQuestionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_if(! in_array($user->role, ['admin', 'instructor']), 403);

        $questions = LessonQuestion::with(['lesson', 'lessonTopic', 'user', 'answeredBy'])
            ->when($user->role === 'instructor', function ($query) use ($user) {
                $query->whereHas('lesson', function ($lessonQuery) use ($user) {
                    $lessonQuery->where('instructor_id', $user->id);
                });
            })
            ->latest()
            ->paginate(10);

        $pendingCount = LessonQuestion::query()
            ->when($user->role === 'instructor', function ($query) use ($user) {
                $query->whereHas('lesson', function ($lessonQuery) use ($user) {
                    $lessonQuery->where('instructor_id', $user->id);
                });
            })
            ->where('visibility', '!=', LessonQuestion::VISIBILITY_HIDDEN)
            ->where(function ($query) {
                $query->whereNull('answer')
                    ->orWhere('status', LessonQuestion::STATUS_PENDING);
            })
            ->count();

        $answeredCount = LessonQuestion::query()
            ->when($user->role === 'instructor', function ($query) use ($user) {
                $query->whereHas('lesson', function ($lessonQuery) use ($user) {
                    $lessonQuery->where('instructor_id', $user->id);
                });
            })
            ->where('visibility', '!=', LessonQuestion::VISIBILITY_HIDDEN)
            ->where(function ($query) {
                $query->whereNotNull('answer')
                    ->orWhere('status', LessonQuestion::STATUS_ANSWERED);
            })
            ->count();

        $publicCount = LessonQuestion::query()
            ->when($user->role === 'instructor', function ($query) use ($user) {
                $query->whereHas('lesson', function ($lessonQuery) use ($user) {
                    $lessonQuery->where('instructor_id', $user->id);
                });
            })
            ->where('visibility', LessonQuestion::VISIBILITY_PUBLIC)
            ->count();

        return view('instructor.questions.index', compact(
            'questions',
            'pendingCount',
            'answeredCount',
            'publicCount'
        ));
    }

    public function show(LessonQuestion $question)
    {
        $this->authorizeInstructorQuestion($question);

        $question->load(['lesson', 'lessonTopic', 'user', 'answeredBy']);

        return view('instructor.questions.show', compact('question'));
    }

    public function update(Request $request, LessonQuestion $question)
    {
        $this->authorizeInstructorQuestion($question);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:3', 'max:5000'],
            'visibility' => ['required', 'string', 'in:private,public,hidden'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $wasUnanswered = blank($question->answer)
            || $question->status !== LessonQuestion::STATUS_ANSWERED;

        $question->update([
            'answer' => $validated['answer'],
            'status' => LessonQuestion::STATUS_ANSWERED,
            'visibility' => $validated['visibility'],
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'is_published' => $request->boolean('is_published', true),
        ]);

        $question->load(['lesson', 'lessonTopic', 'user']);

        if ($wasUnanswered && $question->user) {
            $question->user->notify(new LessonQuestionAnsweredNotification($question));
        }

        return redirect()
            ->route('instructor.questions.index')
            ->with('success', 'Jibu limehifadhiwa kikamilifu.');
    }

    private function authorizeInstructorQuestion(LessonQuestion $question): void
    {
        $user = auth()->user();

        abort_if(! in_array($user->role, ['admin', 'instructor']), 403);

        if ($user->role === 'instructor') {
            abort_if($question->lesson?->instructor_id !== $user->id, 403);
        }
    }
}