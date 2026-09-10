<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    public const PACE_RELAXED = 'relaxed';
    public const PACE_REGULAR = 'regular';
    public const PACE_INTENSIVE = 'intensive';
    public const PACE_CUSTOM = 'custom';

    protected $fillable = [
        'instructor_id',
        'lead_instructor_id',
        'lead_can_receive_students',
        'prerequisite_lesson_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'video_url',
        'pdf',
        'content',
        'category',
        'level',

        // Coursera-style learning schedule
        'estimated_duration_minutes',
        'recommended_study_pace',
        'min_completion_days',
        'max_completion_days',
        'course_deadline',
        'allow_schedule_reset',
        'reminder_days_before_deadline',

        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'lead_can_receive_students' => 'boolean',

        'estimated_duration_minutes' => 'integer',
        'min_completion_days' => 'integer',
        'max_completion_days' => 'integer',
        'course_deadline' => 'date',
        'allow_schedule_reset' => 'boolean',
        'reminder_days_before_deadline' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function leadInstructor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'lead_instructor_id'
        );
    }

    public function followUpInstructors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'lesson_follow_up_instructor',
            'lesson_id',
            'instructor_id'
        )->withTimestamps();
    }

    public function prerequisiteLesson(): BelongsTo
    {
        return $this->belongsTo(
            Lesson::class,
            'prerequisite_lesson_id'
        );
    }

    public function dependentLessons(): HasMany
    {
        return $this->hasMany(
            Lesson::class,
            'prerequisite_lesson_id'
        );
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)
            ->orderBy('order');
    }

    public function publishedModules(): HasMany
    {
        return $this->hasMany(Module::class)
            ->where('is_published', true)
            ->orderBy('order');
    }

    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(
            LessonTopic::class,
            Module::class,
            'lesson_id',
            'module_id',
            'id',
            'id'
        )
            ->orderBy('lesson_topics.order');
    }

    public function publishedTopics(): HasManyThrough
    {
        return $this->hasManyThrough(
            LessonTopic::class,
            Module::class,
            'lesson_id',
            'module_id',
            'id',
            'id'
        )
            ->where('lesson_topics.is_published', true)
            ->orderBy('lesson_topics.order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Final Quiz
    |--------------------------------------------------------------------------
    |
    | Final quiz:
    |
    | lesson_id exists
    | module_id is null
    | lesson_topic_id is null
    |
    */
    public function finalQuiz(): HasOne
    {
        return $this->hasOne(Quiz::class)
            ->whereNull('module_id')
            ->whereNull('lesson_topic_id');
    }

    public function quiz(): HasOne
    {
        return $this->finalQuiz();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LessonEnrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'lesson_enrollments'
        )
            ->withPivot([
                'enrolled_at',
                'follow_up_instructor_id',
                'instructor_assigned_at',
                'study_pace',
                'study_hours_per_week',
                'target_completion_date',
                'schedule_started_at',
                'schedule_updated_at',
            ])
            ->withTimestamps();
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->students();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class)
            ->latest();
    }

    public function publishedQuestions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class)
            ->where('is_published', true)
            ->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Pace Options
    |--------------------------------------------------------------------------
    */

    public static function studyPaces(): array
    {
        return [
            self::PACE_RELAXED => 'Taratibu',
            self::PACE_REGULAR => 'Kawaida',
            self::PACE_INTENSIVE => 'Haraka',
            self::PACE_CUSTOM => 'Ratiba Maalum',
        ];
    }

    public function getPaceLabel(string $pace): string
    {
        return self::studyPaces()[$pace] ?? 'Kawaida';
    }

    public function getPaceHours(
        string $pace,
        ?int $customHours = null
    ): int {
        return match ($pace) {
            self::PACE_RELAXED => 1,
            self::PACE_REGULAR => 3,
            self::PACE_INTENSIVE => 5,

            self::PACE_CUSTOM => max(
                1,
                min(
                    40,
                    (int) ($customHours ?: 3)
                )
            ),

            default => 3,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule Accessors
    |--------------------------------------------------------------------------
    */

    public function getEstimatedDurationHoursAttribute(): int
    {
        return max(
            1,
            (int) ceil(
                ($this->estimated_duration_minutes ?: 180) / 60
            )
        );
    }

    public function getEstimatedDurationLabelAttribute(): string
    {
        $minutesTotal =
            (int) ($this->estimated_duration_minutes ?: 180);

        $hours = intdiv($minutesTotal, 60);
        $minutes = $minutesTotal % 60;

        if ($hours > 0 && $minutes > 0) {
            return $hours
                . ' saa '
                . $minutes
                . ' dakika';
        }

        if ($hours > 0) {
            return $hours . ' saa';
        }

        return $minutes . ' dakika';
    }

    public function getRecommendedStudyPaceLabelAttribute(): string
    {
        return $this->getPaceLabel(
            $this->recommended_study_pace
                ?: self::PACE_REGULAR
        );
    }

    public function getDefaultStudyPaceAttribute(): string
    {
        return $this->recommended_study_pace
            ?: self::PACE_REGULAR;
    }

    public function getDefaultStudyPaceLabelAttribute(): string
    {
        return $this->getPaceLabel(
            $this->default_study_pace
        );
    }

    public function getDefaultStudyHoursPerWeekAttribute(): int
    {
        return $this->getPaceHours(
            $this->default_study_pace
        );
    }

    public function getCourseDeadlineLabelAttribute(): ?string
    {
        return $this->course_deadline
            ? $this->course_deadline->format('d M Y')
            : null;
    }

    public function getHasTimelineRulesAttribute(): bool
    {
        return filled($this->estimated_duration_minutes)
            || filled($this->recommended_study_pace)
            || filled($this->min_completion_days)
            || filled($this->max_completion_days)
            || filled($this->course_deadline)
            || filled($this->reminder_days_before_deadline);
    }

    /*
    |--------------------------------------------------------------------------
    | Coursera-style Schedule Logic
    |--------------------------------------------------------------------------
    */

    public function calculateCompletionDays(
        string $pace,
        ?int $customHours = null
    ): int {
        $estimatedHours =
            $this->estimated_duration_hours;

        $hoursPerWeek = max(
            1,
            $this->getPaceHours(
                $pace,
                $customHours
            )
        );

        if ($estimatedHours <= 3) {
            $daysNeeded = match ($pace) {
                self::PACE_RELAXED => 14,
                self::PACE_REGULAR => 7,
                self::PACE_INTENSIVE => 3,

                self::PACE_CUSTOM => max(
                    1,
                    (int) ceil(
                        ($estimatedHours / $hoursPerWeek) * 7
                    )
                ),

                default => 7,
            };
        } elseif ($estimatedHours <= 10) {
            $daysNeeded = max(
                3,
                (int) ceil(
                    ($estimatedHours / $hoursPerWeek) * 7
                )
            );

            if ($pace === self::PACE_REGULAR) {
                $daysNeeded = max(
                    7,
                    $daysNeeded
                );
            }

            if ($pace === self::PACE_RELAXED) {
                $daysNeeded = max(
                    14,
                    $daysNeeded
                );
            }
        } else {
            $daysNeeded = max(
                7,
                (int) ceil(
                    ($estimatedHours / $hoursPerWeek) * 7
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Completion Days
        |--------------------------------------------------------------------------
        */
        if ($this->min_completion_days) {
            $daysNeeded = max(
                $daysNeeded,
                (int) $this->min_completion_days
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Completion Days
        |--------------------------------------------------------------------------
        */
        if ($this->max_completion_days) {
            $daysNeeded = min(
                $daysNeeded,
                (int) $this->max_completion_days
            );
        }

        return max(
            1,
            $daysNeeded
        );
    }

    public function calculateTargetCompletionDate(
        string $pace,
        ?int $customHours = null
    ) {
        $targetDate = now()->addDays(
            $this->calculateCompletionDays(
                $pace,
                $customHours
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Course Deadline Cap
        |--------------------------------------------------------------------------
        */
        if (
            $this->course_deadline
            && $targetDate->gt($this->course_deadline)
        ) {
            return $this->course_deadline;
        }

        return $targetDate;
    }

    public function formatCompletionDuration(
        int $days
    ): string {
        if ($days <= 1) {
            return 'siku 1';
        }

        if ($days <= 3) {
            return 'siku ' . $days;
        }

        if ($days <= 6) {
            return 'chini ya wiki 1';
        }

        $weeks = (int) ceil(
            $days / 7
        );

        if ($weeks === 1) {
            return 'wiki 1';
        }

        return 'wiki ' . $weeks;
    }

    /*
    |--------------------------------------------------------------------------
    | Completion Labels
    |--------------------------------------------------------------------------
    */

    public function getRelaxedCompletionDaysAttribute(): int
    {
        return $this->calculateCompletionDays(
            self::PACE_RELAXED
        );
    }

    public function getRegularCompletionDaysAttribute(): int
    {
        return $this->calculateCompletionDays(
            self::PACE_REGULAR
        );
    }

    public function getIntensiveCompletionDaysAttribute(): int
    {
        return $this->calculateCompletionDays(
            self::PACE_INTENSIVE
        );
    }

    public function getDefaultCompletionDaysAttribute(): int
    {
        return $this->calculateCompletionDays(
            $this->default_study_pace
        );
    }

    public function getRelaxedCompletionLabelAttribute(): string
    {
        return $this->formatCompletionDuration(
            $this->relaxed_completion_days
        );
    }

    public function getRegularCompletionLabelAttribute(): string
    {
        return $this->formatCompletionDuration(
            $this->regular_completion_days
        );
    }

    public function getIntensiveCompletionLabelAttribute(): string
    {
        return $this->formatCompletionDuration(
            $this->intensive_completion_days
        );
    }

    public function getDefaultCompletionLabelAttribute(): string
    {
        return $this->formatCompletionDuration(
            $this->default_completion_days
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prerequisite Helpers
    |--------------------------------------------------------------------------
    */

    public function hasPrerequisite(): bool
    {
        return filled(
            $this->prerequisite_lesson_id
        );
    }

    public function prerequisiteTitle(): ?string
    {
        return $this->prerequisiteLesson?->title;
    }

    /*
    |--------------------------------------------------------------------------
    | Published Module Completion
    |--------------------------------------------------------------------------
    |
    | A lesson does NOT use topic counts directly anymore.
    |
    | Each Module::isCompletedBy() decides whether that module has been
    | completed.
    |
    | Current module rule:
    |
    | - All published topics completed.
    | - Required module quiz attempted.
    |
    */
    public function completedModulesCountFor(
        ?User $user
    ): int {
        if (! $user) {
            return 0;
        }

        return $this->publishedModules()
            ->get()
            ->filter(
                fn ($module) =>
                    $module->isCompletedBy($user)
            )
            ->count();
    }

    public function incompleteModulesCountFor(
        ?User $user
    ): int {
        if (! $user) {
            return $this->publishedModules()
                ->count();
        }

        return $this->publishedModules()
            ->get()
            ->reject(
                fn ($module) =>
                    $module->isCompletedBy($user)
            )
            ->count();
    }

    public function modulesWithQuizPendingCountFor(
        ?User $user
    ): int {
        if (! $user) {
            return 0;
        }

        return $this->publishedModules()
            ->get()
            ->filter(
                fn ($module) =>
                    $module->completionStatusFor($user)
                    === 'quiz_pending'
            )
            ->count();
    }

    public function areAllModulesCompletedBy(
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        $modules = $this->publishedModules()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | No Published Modules
        |--------------------------------------------------------------------------
        |
        | Empty lesson should not automatically count as completed.
        |
        */
        if ($modules->isEmpty()) {
            return false;
        }

        foreach ($modules as $module) {
            if (! $module->isCompletedBy($user)) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Final Quiz Helpers
    |--------------------------------------------------------------------------
    */

    public function getPublishedFinalQuiz(): ?Quiz
    {
        return $this->finalQuiz()
            ->where('is_published', true)
            ->first();
    }

    public function hasRequiredFinalQuiz(): bool
    {
        $quiz = $this->getPublishedFinalQuiz();

        return (bool) (
            $quiz
            && $quiz->is_required
        );
    }

    public function hasPassedRequiredFinalQuizBy(
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        $finalQuiz = $this->getPublishedFinalQuiz();

        /*
        |--------------------------------------------------------------------------
        | No Required Final Quiz
        |--------------------------------------------------------------------------
        |
        | If there is no final quiz, or the final quiz is optional,
        | this requirement is automatically satisfied.
        |
        */
        if (
            ! $finalQuiz
            || ! $finalQuiz->is_required
        ) {
            return true;
        }

        return QuizResult::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $finalQuiz->id)
            ->where('passed', true)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Lesson Completion
    |--------------------------------------------------------------------------
    |
    | Whole lesson completion:
    |
    | Every published module completed
    | +
    | Required final quiz passed
    | =
    | Lesson completed
    |
    */
    public function isCompletedBy(
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | All Published Modules Must Be Complete
        |--------------------------------------------------------------------------
        */
        if (! $this->areAllModulesCompletedBy($user)) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Required Final Quiz Must Be Passed
        |--------------------------------------------------------------------------
        */
        if (! $this->hasPassedRequiredFinalQuizBy($user)) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Lesson Completion Status
    |--------------------------------------------------------------------------
    */

    public function completionStatusFor(
        ?User $user
    ): string {
        if (! $user) {
            return 'not_started';
        }

        $modules = $this->publishedModules()
            ->get();

        if ($modules->isEmpty()) {
            return 'not_started';
        }

        if ($this->isCompletedBy($user)) {
            return 'completed';
        }

        $quizPendingCount =
            $modules
                ->filter(
                    fn ($module) =>
                        $module->completionStatusFor($user)
                        === 'quiz_pending'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Module Quiz Pending
        |--------------------------------------------------------------------------
        */
        if ($quizPendingCount > 0) {
            return 'module_quiz_pending';
        }

        /*
        |--------------------------------------------------------------------------
        | All Modules Complete But Final Quiz Pending
        |--------------------------------------------------------------------------
        */
        if (
            $this->areAllModulesCompletedBy($user)
            && $this->hasRequiredFinalQuiz()
            && ! $this->hasPassedRequiredFinalQuizBy($user)
        ) {
            return 'final_quiz_pending';
        }

        /*
        |--------------------------------------------------------------------------
        | Check Whether Learning Has Started
        |--------------------------------------------------------------------------
        */
        $hasProgress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $this->id)
            ->exists();

        if ($hasProgress) {
            return 'in_progress';
        }

        return 'not_started';
    }

    public function completionLabelFor(
        ?User $user
    ): string {
        return match (
            $this->completionStatusFor($user)
        ) {
            'completed' =>
                'Limekamilika',

            'module_quiz_pending' =>
                'Quiz ya module inasubiri',

            'final_quiz_pending' =>
                'Jaribio la mwisho linasubiri',

            'in_progress' =>
                'Unaendelea',

            default =>
                'Halijaanza',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Lesson Prerequisite Logic
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Students may move freely between modules INSIDE one lesson.
    |
    | However, if another lesson requires this lesson as a prerequisite,
    | the next lesson stays locked until this whole lesson is completed.
    |
    */
    public function canBeStartedBy(
        ?User $user
    ): bool {
        if (! $this->hasPrerequisite()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        $prerequisite =
            $this->prerequisiteLesson;

        /*
        |--------------------------------------------------------------------------
        | Missing Prerequisite Record
        |--------------------------------------------------------------------------
        |
        | Preserve existing behavior:
        | if the referenced lesson no longer exists, do not permanently lock
        | the student out.
        |
        */
        if (! $prerequisite) {
            return true;
        }

        return $prerequisite
            ->isCompletedBy($user);
    }

    public function getIsLockedForCurrentUserAttribute(): bool
    {
        return ! $this->canBeStartedBy(
            auth()->user()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Useful Helpers
    |--------------------------------------------------------------------------
    */

    public function isPastDeadline(): bool
    {
        return $this->course_deadline
            ? now()
                ->startOfDay()
                ->gt($this->course_deadline)
            : false;
    }

    public function isDeadlineNear(): bool
    {
        if (
            ! $this->course_deadline
            || ! $this->reminder_days_before_deadline
        ) {
            return false;
        }

        return now()
            ->startOfDay()
            ->diffInDays(
                $this->course_deadline,
                false
            )
            <= $this->reminder_days_before_deadline;
    }
}