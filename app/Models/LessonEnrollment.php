<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'follow_up_instructor_id',
        'instructor_assigned_at',
        'enrolled_at',

        // Coursera-style learning schedule
        'study_pace',
        'study_hours_per_week',
        'target_completion_date',
        'schedule_started_at',
        'schedule_updated_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'instructor_assigned_at' => 'datetime',
        'target_completion_date' => 'datetime',
        'schedule_started_at' => 'datetime',
        'schedule_updated_at' => 'datetime',
        'study_hours_per_week' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function followUpInstructor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'follow_up_instructor_id'
        );
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(
            LessonReminderLog::class,
            'user_id',
            'user_id'
        )->where(
            'lesson_id',
            $this->lesson_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule Labels
    |--------------------------------------------------------------------------
    */

    public function getStudyPaceLabelAttribute(): string
    {
        return match ($this->study_pace) {
            Lesson::PACE_RELAXED => 'Taratibu',
            Lesson::PACE_REGULAR => 'Kawaida',
            Lesson::PACE_INTENSIVE => 'Haraka',
            Lesson::PACE_CUSTOM => 'Ratiba Maalum',
            default => 'Kawaida',
        };
    }

    public function getStudyHoursLabelAttribute(): ?string
    {
        if (! $this->study_hours_per_week) {
            return null;
        }

        return $this->study_hours_per_week . ' saa kwa wiki';
    }

    public function getTargetCompletionDateLabelAttribute(): ?string
    {
        if (! $this->target_completion_date) {
            return null;
        }

        return $this->target_completion_date->format('d M Y');
    }

    public function getEnrolledAtLabelAttribute(): ?string
    {
        if (! $this->enrolled_at) {
            return null;
        }

        return $this->enrolled_at->format('d M Y, H:i');
    }

    public function getScheduleStartedAtLabelAttribute(): ?string
    {
        if (! $this->schedule_started_at) {
            return null;
        }

        return $this->schedule_started_at->format('d M Y, H:i');
    }

    public function getScheduleUpdatedAtLabelAttribute(): ?string
    {
        if (! $this->schedule_updated_at) {
            return null;
        }

        return $this->schedule_updated_at->format('d M Y, H:i');
    }

    /*
    |--------------------------------------------------------------------------
    | Completion Helpers
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Lesson completion is NOT based only on topic count.
    |
    | The Lesson model is now the source of truth:
    |
    | 1. All published topics must be completed.
    | 2. Required module quizzes must be attempted.
    | 3. Required final quiz must be passed.
    |
    */

    public function getIsCompletedAttribute(): bool
    {
        if (! $this->lesson || ! $this->user) {
            return false;
        }

        return $this->lesson->isCompletedBy(
            $this->user
        );
    }

    public function getCompletionStatusAttribute(): string
    {
        if (! $this->lesson || ! $this->user) {
            return 'not_started';
        }

        return $this->lesson->completionStatusFor(
            $this->user
        );
    }

    public function getCompletionLabelAttribute(): string
    {
        if (! $this->lesson || ! $this->user) {
            return 'Haijaanza';
        }

        return $this->lesson->completionLabelFor(
            $this->user
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Module Progress
    |--------------------------------------------------------------------------
    */

    public function getTotalModulesAttribute(): int
    {
        if (! $this->lesson) {
            return 0;
        }

        return $this->lesson
            ->modules()
            ->where('is_published', true)
            ->count();
    }

    public function getCompletedModulesAttribute(): int
    {
        if (! $this->lesson || ! $this->user) {
            return 0;
        }

        return $this->lesson->completedModulesCountFor(
            $this->user
        );
    }

    public function getIncompleteModulesAttribute(): int
    {
        if (! $this->lesson || ! $this->user) {
            return 0;
        }

        return $this->lesson->incompleteModulesCountFor(
            $this->user
        );
    }

    public function getModulesWithQuizPendingAttribute(): int
    {
        if (! $this->lesson || ! $this->user) {
            return 0;
        }

        return $this->lesson->modulesWithQuizPendingCountFor(
            $this->user
        );
    }

    public function getModulesProgressPercentAttribute(): int
    {
        if ($this->total_modules <= 0) {
            return 0;
        }

        return (int) round(
            ($this->completed_modules / $this->total_modules) * 100
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Topic Learning Progress
    |--------------------------------------------------------------------------
    |
    | This represents CONTENT progress only.
    |
    | Example:
    | A student may have completed 100% of topics but still have a required
    | module quiz pending. In that case:
    |
    | learning_progress_percent = 100
    | is_completed = false
    |
    */

    public function getTotalTopicsAttribute(): int
    {
        if (! $this->lesson) {
            return 0;
        }

        return LessonTopic::query()
            ->whereHas(
                'module',
                function ($query) {
                    $query
                        ->where(
                            'lesson_id',
                            $this->lesson_id
                        )
                        ->where(
                            'is_published',
                            true
                        );
                }
            )
            ->where(
                'is_published',
                true
            )
            ->count();
    }

    public function getCompletedTopicsAttribute(): int
    {
        if (
            ! $this->lesson_id
            || ! $this->user_id
        ) {
            return 0;
        }

        $publishedTopicIds = LessonTopic::query()
            ->whereHas(
                'module',
                function ($query) {
                    $query
                        ->where(
                            'lesson_id',
                            $this->lesson_id
                        )
                        ->where(
                            'is_published',
                            true
                        );
                }
            )
            ->where(
                'is_published',
                true
            )
            ->pluck('id');

        if ($publishedTopicIds->isEmpty()) {
            return 0;
        }

        return LessonProgress::query()
            ->where(
                'user_id',
                $this->user_id
            )
            ->where(
                'lesson_id',
                $this->lesson_id
            )
            ->whereIn(
                'lesson_topic_id',
                $publishedTopicIds
            )
            ->distinct()
            ->count(
                'lesson_topic_id'
            );
    }

    public function getLearningProgressPercentAttribute(): int
    {
        if ($this->total_topics <= 0) {
            return 0;
        }

        return (int) round(
            ($this->completed_topics / $this->total_topics) * 100
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Backwards-Compatible Progress Attribute
    |--------------------------------------------------------------------------
    |
    | Existing dashboard/admin code may still use:
    |
    | $enrollment->progress_percent
    |
    | Keep it working, but understand that this is learning/topic progress,
    | not final lesson completion.
    |
    */

    public function getProgressPercentAttribute(): int
    {
        return $this->learning_progress_percent;
    }

    /*
    |--------------------------------------------------------------------------
    | Quiz Status Helpers
    |--------------------------------------------------------------------------
    */

    public function getHasModuleQuizPendingAttribute(): bool
    {
        return $this->modules_with_quiz_pending > 0;
    }

    public function getHasFinalQuizPendingAttribute(): bool
    {
        return $this->completion_status === 'final_quiz_pending';
    }

    public function getHasAnyQuizPendingAttribute(): bool
    {
        return $this->has_module_quiz_pending
            || $this->has_final_quiz_pending;
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule Status
    |--------------------------------------------------------------------------
    */

    public function getRemainingDaysAttribute(): ?int
    {
        if (! $this->target_completion_date) {
            return null;
        }

        return now()
            ->startOfDay()
            ->diffInDays(
                $this->target_completion_date
                    ->copy()
                    ->startOfDay(),
                false
            );
    }

    public function getRemainingDaysLabelAttribute(): ?string
    {
        if (is_null($this->remaining_days)) {
            return null;
        }

        if ($this->is_completed) {
            return 'Somo limekamilika';
        }

        if ($this->remaining_days < 0) {
            return 'Umepita kwa siku '
                . abs($this->remaining_days);
        }

        if ($this->remaining_days === 0) {
            return 'Leo';
        }

        if ($this->remaining_days === 1) {
            return 'Siku 1 imebaki';
        }

        return 'Siku '
            . $this->remaining_days
            . ' zimebaki';
    }

    public function getIsBehindScheduleAttribute(): bool
    {
        /*
        |--------------------------------------------------------------------------
        | Completed Lessons Are Never Behind Schedule
        |--------------------------------------------------------------------------
        */
        if ($this->is_completed) {
            return false;
        }

        if (! $this->target_completion_date) {
            return false;
        }

        return now()
            ->startOfDay()
            ->greaterThan(
                $this->target_completion_date
                    ->copy()
                    ->startOfDay()
            );
    }

    public function getIsDueTodayAttribute(): bool
    {
        if ($this->is_completed) {
            return false;
        }

        if (! $this->target_completion_date) {
            return false;
        }

        return now()
            ->startOfDay()
            ->equalTo(
                $this->target_completion_date
                    ->copy()
                    ->startOfDay()
            );
    }

    public function getIsOnTrackAttribute(): bool
    {
        if ($this->is_completed) {
            return true;
        }

        return $this->hasSchedule()
            && ! $this->is_behind_schedule
            && ! $this->is_due_today;
    }

    public function getScheduleStatusLabelAttribute(): string
    {
        if ($this->is_completed) {
            return 'Imekamilika';
        }

        if (! $this->hasSchedule()) {
            return 'Hakuna ratiba';
        }

        if ($this->is_behind_schedule) {
            return 'Umechelewa';
        }

        if ($this->is_due_today) {
            return 'Lengo ni leo';
        }

        return 'Unaendelea vizuri';
    }

    public function getScheduleStatusDescriptionAttribute(): string
    {
        if ($this->is_completed) {
            return 'Hongera! Umekamilisha somo hili.';
        }

        if (! $this->hasSchedule()) {
            return 'Mwanafunzi bado hajapangiwa ratiba ya kujifunza.';
        }

        if ($this->is_behind_schedule) {
            return 'Umepita tarehe ya lengo lako. Endelea kujifunza au badili ratiba kama mfumo unaruhusu.';
        }

        if ($this->is_due_today) {
            return 'Leo ndiyo siku ya lengo lako la kukamilisha somo hili.';
        }

        return 'Ratiba yako ipo sawa kulingana na tarehe ya lengo lako.';
    }

    public function getScheduleStatusColorAttribute(): string
    {
        if ($this->is_completed) {
            return 'green';
        }

        if (! $this->hasSchedule()) {
            return 'gray';
        }

        if ($this->is_behind_schedule) {
            return 'red';
        }

        if ($this->is_due_today) {
            return 'yellow';
        }

        return 'green';
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function hasSchedule(): bool
    {
        return filled(
            $this->study_pace
        )
            && filled(
                $this->study_hours_per_week
            )
            && filled(
                $this->target_completion_date
            );
    }

    public function canResetSchedule(): bool
    {
        if (! $this->lesson) {
            return false;
        }

        return (bool) $this->lesson
            ->allow_schedule_reset;
    }

    public function resetSchedule(
        string $pace,
        ?int $customHours = null
    ): void {
        if (! $this->lesson) {
            return;
        }

        $pace = $this->normalizePace(
            $pace
        );

        $hoursPerWeek = $this->lesson
            ->getPaceHours(
                $pace,
                $customHours
            );

        $targetCompletionDate = $this->lesson
            ->calculateTargetCompletionDate(
                $pace,
                $customHours
            );

        $this->forceFill([
            'study_pace' => $pace,
            'study_hours_per_week' => $hoursPerWeek,
            'target_completion_date' => $targetCompletionDate,
            'schedule_started_at' => $this->schedule_started_at ?: now(),
            'schedule_updated_at' => now(),
        ])->save();
    }

    public function normalizePace(
        ?string $pace
    ): string {
        return in_array(
            $pace,
            [
                Lesson::PACE_RELAXED,
                Lesson::PACE_REGULAR,
                Lesson::PACE_INTENSIVE,
                Lesson::PACE_CUSTOM,
            ],
            true
        )
            ? $pace
            : Lesson::PACE_REGULAR;
    }

    public static function createForLesson(
        User $user,
        Lesson $lesson,
        string $pace,
        ?int $customHours = null
    ): self {
        $pace = in_array(
            $pace,
            [
                Lesson::PACE_RELAXED,
                Lesson::PACE_REGULAR,
                Lesson::PACE_INTENSIVE,
                Lesson::PACE_CUSTOM,
            ],
            true
        )
            ? $pace
            : Lesson::PACE_REGULAR;

        $hoursPerWeek = $lesson
            ->getPaceHours(
                $pace,
                $customHours
            );

        return self::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'enrolled_at' => now(),
                'study_pace' => $pace,
                'study_hours_per_week' => $hoursPerWeek,
                'target_completion_date' => $lesson
                    ->calculateTargetCompletionDate(
                        $pace,
                        $customHours
                    ),
                'schedule_started_at' => now(),
                'schedule_updated_at' => now(),
            ]
        );
    }
}