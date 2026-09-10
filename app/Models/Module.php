<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'order',
        'is_published',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_published' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function topics()
    {
        return $this->hasMany(LessonTopic::class, 'module_id')
            ->orderBy('order');
    }

    public function publishedTopics()
    {
        return $this->hasMany(LessonTopic::class, 'module_id')
            ->where('is_published', true)
            ->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function publishedQuiz()
    {
        return $this->hasOne(Quiz::class)
            ->where('is_published', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Completion Helpers
    |--------------------------------------------------------------------------
    |
    | Important:
    |
    | - Module completion does NOT control access to another module.
    | - A student may continue to later modules even if this module is incomplete.
    | - A module becomes complete when:
    |
    |   1. All published topics are completed.
    |   2. If there is a required published module quiz, the student has
    |      attempted that quiz.
    |
    | A quiz does not have to be passed for module completion under the
    | current rule. It only needs to have been taken.
    |
    */

    public function getPublishedTopicsCountAttribute(): int
    {
        return $this->publishedTopics()->count();
    }

    public function completedTopicsCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $topicIds = $this->publishedTopics()
            ->pluck('id');

        if ($topicIds->isEmpty()) {
            return 0;
        }

        return LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_topic_id', $topicIds)
            ->distinct('lesson_topic_id')
            ->count('lesson_topic_id');
    }

    public function areAllTopicsCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $totalTopics = $this->publishedTopics()->count();

        /*
        |--------------------------------------------------------------------------
        | Empty Module
        |--------------------------------------------------------------------------
        |
        | Do not automatically mark an empty module as completed.
        |
        */
        if ($totalTopics === 0) {
            return false;
        }

        return $this->completedTopicsCountFor($user) >= $totalTopics;
    }

    /*
    |--------------------------------------------------------------------------
    | Required Module Quiz
    |--------------------------------------------------------------------------
    |
    | A "module quiz" is attached to the module and is not attached directly
    | to a lesson topic.
    |
    */

    public function requiredPublishedQuiz(): ?Quiz
    {
        return $this->quizzes()
            ->where('is_published', true)
            ->where('is_required', true)
            ->whereNull('lesson_topic_id')
            ->orderBy('id')
            ->first();
    }

    public function hasRequiredPublishedQuiz(): bool
    {
        return $this->requiredPublishedQuiz() !== null;
    }

    public function hasRequiredQuizAttemptBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $quiz = $this->requiredPublishedQuiz();

        /*
        |--------------------------------------------------------------------------
        | No Required Quiz
        |--------------------------------------------------------------------------
        |
        | If this module has no required quiz, there is no quiz requirement
        | preventing module completion.
        |
        */
        if (! $quiz) {
            return true;
        }

        return QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function isRequiredQuizPassedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $quiz = $this->requiredPublishedQuiz();

        if (! $quiz) {
            return true;
        }

        return QuizResult::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Module Completion
    |--------------------------------------------------------------------------
    |
    | Current rule requested:
    |
    | All topics completed
    | +
    | Required module quiz ATTEMPTED
    | =
    | Module completed
    |
    | The student is still free to continue to another module while this
    | module remains incomplete.
    |
    */

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->areAllTopicsCompletedBy($user)) {
            return false;
        }

        return $this->hasRequiredQuizAttemptBy($user);
    }

    /*
    |--------------------------------------------------------------------------
    | Module Status
    |--------------------------------------------------------------------------
    */

    public function completionStatusFor(?User $user): string
    {
        if (! $user) {
            return 'not_started';
        }

        $totalTopics = $this->publishedTopics()->count();
        $completedTopics = $this->completedTopicsCountFor($user);

        if ($this->isCompletedBy($user)) {
            return 'completed';
        }

        if ($totalTopics > 0 && $completedTopics >= $totalTopics) {
            if (
                $this->hasRequiredPublishedQuiz()
                && ! $this->hasRequiredQuizAttemptBy($user)
            ) {
                return 'quiz_pending';
            }

            return 'incomplete';
        }

        if ($completedTopics > 0) {
            return 'in_progress';
        }

        return 'not_started';
    }

    public function completionLabelFor(?User $user): string
    {
        return match ($this->completionStatusFor($user)) {
            'completed' => 'Imekamilika',
            'quiz_pending' => 'Quiz bado haijafanywa',
            'in_progress' => 'Inaendelea',
            'incomplete' => 'Haijakamilika',
            default => 'Haijaanza',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Progress Percentage
    |--------------------------------------------------------------------------
    |
    | This percentage represents topic learning progress only.
    |
    | Example:
    |
    | Topics: 100%
    | Module status: Quiz pending
    |
    | This avoids pretending that a pending quiz means the topics themselves
    | have not been studied.
    |
    */

    public function progressPercentageFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $totalTopics = $this->publishedTopics()->count();

        if ($totalTopics === 0) {
            return 0;
        }

        $completedTopics = $this->completedTopicsCountFor($user);

        return (int) round(
            min(100, ($completedTopics / $totalTopics) * 100)
        );
    }
}