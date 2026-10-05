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

    public function getPublishedTopicsCountAttribute(): int
    {
        return $this->publishedTopics()->count();
    }

    public function completedTopicsCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $topicIds = $this->publishedTopics()->pluck('id');

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

        if ($totalTopics === 0) {
            return false;
        }

        return $this->completedTopicsCountFor($user) >= $totalTopics;
    }

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

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->areAllTopicsCompletedBy($user)) {
            return false;
        }

        return $this->isRequiredQuizPassedBy($user);
    }

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
            if ($this->hasRequiredPublishedQuiz()) {
                if (! $this->hasRequiredQuizAttemptBy($user)) {
                    return 'quiz_pending';
                }

                if (! $this->isRequiredQuizPassedBy($user)) {
                    return 'quiz_failed';
                }
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
            'quiz_failed' => 'Quiz haijafaulu',
            'in_progress' => 'Inaendelea',
            'incomplete' => 'Haijakamilika',
            default => 'Haijaanza',
        };
    }

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
