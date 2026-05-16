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

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(LessonReminderLog::class, 'user_id', 'user_id')
            ->where('lesson_id', $this->lesson_id);
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
                $this->target_completion_date->copy()->startOfDay(),
                false
            );
    }

    public function getRemainingDaysLabelAttribute(): ?string
    {
        if (is_null($this->remaining_days)) {
            return null;
        }

        if ($this->remaining_days < 0) {
            return 'Umepita kwa siku ' . abs($this->remaining_days);
        }

        if ($this->remaining_days === 0) {
            return 'Leo';
        }

        if ($this->remaining_days === 1) {
            return 'Siku 1 imebaki';
        }

        return 'Siku ' . $this->remaining_days . ' zimebaki';
    }

    public function getIsBehindScheduleAttribute(): bool
    {
        if (! $this->target_completion_date) {
            return false;
        }

        return now()
            ->startOfDay()
            ->greaterThan(
                $this->target_completion_date->copy()->startOfDay()
            );
    }

    public function getIsDueTodayAttribute(): bool
    {
        if (! $this->target_completion_date) {
            return false;
        }

        return now()
            ->startOfDay()
            ->equalTo(
                $this->target_completion_date->copy()->startOfDay()
            );
    }

    public function getIsOnTrackAttribute(): bool
    {
        return $this->hasSchedule()
            && ! $this->is_behind_schedule
            && ! $this->is_due_today;
    }

    public function getScheduleStatusLabelAttribute(): string
    {
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
    | Progress Helpers
    |--------------------------------------------------------------------------
    */

    public function getTotalTopicsAttribute(): int
    {
        if (! $this->lesson) {
            return 0;
        }

        return $this->lesson
            ->topics()
            ->count();
    }

    public function getCompletedTopicsAttribute(): int
    {
        if (! $this->lesson_id || ! $this->user_id) {
            return 0;
        }

        return LessonProgress::query()
            ->where('user_id', $this->user_id)
            ->where('lesson_id', $this->lesson_id)
            ->distinct('lesson_topic_id')
            ->count('lesson_topic_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_topics <= 0) {
            return 0;
        }

        return (int) round(($this->completed_topics / $this->total_topics) * 100);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->total_topics > 0
            && $this->completed_topics >= $this->total_topics;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function hasSchedule(): bool
    {
        return filled($this->study_pace)
            && filled($this->study_hours_per_week)
            && filled($this->target_completion_date);
    }

    public function canResetSchedule(): bool
    {
        if (! $this->lesson) {
            return false;
        }

        return (bool) $this->lesson->allow_schedule_reset;
    }

    public function resetSchedule(string $pace, ?int $customHours = null): void
    {
        if (! $this->lesson) {
            return;
        }

        $pace = $this->normalizePace($pace);

        $hoursPerWeek = $this->lesson->getPaceHours($pace, $customHours);

        $targetCompletionDate = $this->lesson->calculateTargetCompletionDate(
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

    public function normalizePace(?string $pace): string
    {
        return in_array($pace, [
            Lesson::PACE_RELAXED,
            Lesson::PACE_REGULAR,
            Lesson::PACE_INTENSIVE,
            Lesson::PACE_CUSTOM,
        ], true)
            ? $pace
            : Lesson::PACE_REGULAR;
    }

    public static function createForLesson(User $user, Lesson $lesson, string $pace, ?int $customHours = null): self
    {
        $pace = in_array($pace, [
            Lesson::PACE_RELAXED,
            Lesson::PACE_REGULAR,
            Lesson::PACE_INTENSIVE,
            Lesson::PACE_CUSTOM,
        ], true)
            ? $pace
            : Lesson::PACE_REGULAR;

        $hoursPerWeek = $lesson->getPaceHours($pace, $customHours);

        return self::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'enrolled_at' => now(),
                'study_pace' => $pace,
                'study_hours_per_week' => $hoursPerWeek,
                'target_completion_date' => $lesson->calculateTargetCompletionDate($pace, $customHours),
                'schedule_started_at' => now(),
                'schedule_updated_at' => now(),
            ]
        );
    }
}