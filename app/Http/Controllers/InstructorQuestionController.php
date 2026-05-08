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

        $questions = LessonQuestion::with(['lesson', 'user', 'answeredBy'])
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
            ->whereNull('answer')
            ->count();

        $answeredCount = LessonQuestion::query()
            ->when($user->role === 'instructor', function ($query) use ($user) {
                $query->whereHas('lesson', function ($lessonQuery) use ($user) {
                    $lessonQuery->where('instructor_id', $user->id);
                });
            })
            ->whereNotNull('answer')
            ->count();

        return view('instructor.questions.index', compact(
            'questions',
            'pendingCount',
            'answeredCount'
        ));
    }

    public function show(LessonQuestion $question)
    {
        $this->authorizeInstructorQuestion($question);

        $question->load(['lesson', 'user', 'answeredBy']);

        return view('instructor.questions.show', compact('question'));
    }

    public function update(Request $request, LessonQuestion $question)
    {
        $this->authorizeInstructorQuestion($question);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:3', 'max:5000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $wasUnanswered = blank($question->answer);

        $question->update([
            'answer' => $validated['answer'],
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'is_published' => $request->boolean('is_published', true),
        ]);

        $question->load(['lesson', 'user']);

        /*
        |--------------------------------------------------------------------------
        | Notify student when question is answered
        |--------------------------------------------------------------------------
        | Send only when the question was previously unanswered.
        */
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