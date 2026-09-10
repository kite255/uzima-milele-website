<?php

namespace App\Http\Controllers;

use App\Models\LessonQuestion;
use App\Notifications\LessonQuestionAnsweredNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InstructorQuestionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_if(
            ! $user
            || ! in_array(
                $user->role,
                ['admin', 'instructor'],
                true
            ),
            403
        );

        $questionsQuery = LessonQuestion::query()
            ->with([
                'lesson',
                'lessonTopic',
                'user',
                'answeredBy',
            ]);

        if ($user->role === 'instructor') {
            $this->applyInstructorAccessScope(
                $questionsQuery,
                $user->id
            );
        }

        $questions = $questionsQuery
            ->latest()
            ->paginate(10);

        $pendingQuery = LessonQuestion::query();

        if ($user->role === 'instructor') {
            $this->applyInstructorAccessScope(
                $pendingQuery,
                $user->id
            );
        }

        $pendingCount = $pendingQuery
            ->where(
                'visibility',
                '!=',
                LessonQuestion::VISIBILITY_HIDDEN
            )
            ->where(function (Builder $query) {
                $query
                    ->whereNull('answer')
                    ->orWhere(
                        'status',
                        LessonQuestion::STATUS_PENDING
                    );
            })
            ->count();

        $answeredQuery = LessonQuestion::query();

        if ($user->role === 'instructor') {
            $this->applyInstructorAccessScope(
                $answeredQuery,
                $user->id
            );
        }

        $answeredCount = $answeredQuery
            ->where(
                'visibility',
                '!=',
                LessonQuestion::VISIBILITY_HIDDEN
            )
            ->where(function (Builder $query) {
                $query
                    ->whereNotNull('answer')
                    ->orWhere(
                        'status',
                        LessonQuestion::STATUS_ANSWERED
                    );
            })
            ->count();

        $publicQuery = LessonQuestion::query();

        if ($user->role === 'instructor') {
            $this->applyInstructorAccessScope(
                $publicQuery,
                $user->id
            );
        }

        $publicCount = $publicQuery
            ->where(
                'visibility',
                LessonQuestion::VISIBILITY_PUBLIC
            )
            ->count();

        return view(
            'instructor.questions.index',
            compact(
                'questions',
                'pendingCount',
                'answeredCount',
                'publicCount'
            )
        );
    }

    public function show(LessonQuestion $question)
    {
        $this->authorizeInstructorQuestion($question);

        $question->load([
            'lesson',
            'lessonTopic',
            'user',
            'answeredBy',
        ]);

        return view(
            'instructor.questions.show',
            compact('question')
        );
    }

    public function update(
        Request $request,
        LessonQuestion $question
    ) {
        $this->authorizeInstructorQuestion($question);

        $validated = $request->validate([
            'answer' => [
                'required',
                'string',
                'min:3',
                'max:5000',
            ],
            'visibility' => [
                'required',
                'string',
                'in:private,public,hidden',
            ],
            'is_published' => [
                'nullable',
                'boolean',
            ],
        ]);

        $wasUnanswered =
            blank($question->answer)
            || $question->status
                !== LessonQuestion::STATUS_ANSWERED;

        $question->update([
            'answer' => $validated['answer'],
            'status' => LessonQuestion::STATUS_ANSWERED,
            'visibility' => $validated['visibility'],
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'is_published' =>
                $request->boolean(
                    'is_published',
                    true
                ),
        ]);

        $question->load([
            'lesson',
            'lessonTopic',
            'user',
        ]);

        if (
            $wasUnanswered
            && $question->user
        ) {
            $question->user->notify(
                new LessonQuestionAnsweredNotification(
                    $question
                )
            );
        }

        return redirect()
            ->route(
                'instructor.questions.index'
            )
            ->with(
                'success',
                'Jibu limehifadhiwa kikamilifu.'
            );
    }

    private function authorizeInstructorQuestion(
        LessonQuestion $question
    ): void {
        $user = auth()->user();

        abort_if(
            ! $user
            || ! in_array(
                $user->role,
                ['admin', 'instructor'],
                true
            ),
            403
        );

        if ($user->role !== 'instructor') {
            return;
        }

        $allowed = LessonQuestion::query()
            ->whereKey($question->id);

        $this->applyInstructorAccessScope(
            $allowed,
            $user->id
        );

        abort_if(
            ! $allowed->exists(),
            403
        );
    }

    private function applyInstructorAccessScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $questionQuery) use ($instructorId) {
                $questionQuery
                    /*
                    |--------------------------------------------------------------------------
                    | Lead Instructor
                    |--------------------------------------------------------------------------
                    */
                    ->whereHas(
                        'lesson',
                        fn (Builder $lessonQuery) =>
                            $lessonQuery->where(
                                'lead_instructor_id',
                                $instructorId
                            )
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | Assigned Follow-up Instructor
                    |--------------------------------------------------------------------------
                    |
                    | Match both lesson and student so instructors see only
                    | questions from students assigned directly to them.
                    |
                    */
                    ->orWhereExists(
                        function ($enrollmentQuery) use ($instructorId) {
                            $enrollmentQuery
                                ->selectRaw('1')
                                ->from('lesson_enrollments')
                                ->whereColumn(
                                    'lesson_enrollments.lesson_id',
                                    'lesson_questions.lesson_id'
                                )
                                ->whereColumn(
                                    'lesson_enrollments.user_id',
                                    'lesson_questions.user_id'
                                )
                                ->where(
                                    'lesson_enrollments.follow_up_instructor_id',
                                    $instructorId
                                );
                        }
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Compatibility
                    |--------------------------------------------------------------------------
                    */
                    ->orWhereHas(
                        'lesson',
                        function (Builder $lessonQuery) use ($instructorId) {
                            $lessonQuery
                                ->where(
                                    'instructor_id',
                                    $instructorId
                                )
                                ->whereNull(
                                    'lead_instructor_id'
                                )
                                ->whereDoesntHave(
                                    'followUpInstructors'
                                );
                        }
                    );
            }
        );
    }
}
