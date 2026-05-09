<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'video_url',
        'pdf',
        'content',
        'category',
        'level',

        // Course timeline settings
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
        'estimated_duration_minutes' => 'integer',
        'min_completion_days' => 'integer',
        'max_completion_days' => 'integer',
        'course_deadline' => 'date',
        'allow_schedule_reset' => 'boolean',
        'reminder_days_before_deadline' => 'integer',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function topics()
    {
        return $this->hasManyThrough(
            LessonTopic::class,
            Module::class,
            'lesson_id',
            'module_id',
            'id',
            'id'
        )->orderBy('lesson_topics.order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function finalQuiz()
    {
        return $this->hasOne(Quiz::class)
            ->whereNull('module_id')
            ->whereNull('lesson_topic_id');
    }

    public function quiz()
    {
        return $this->finalQuiz();
    }

    public function publishedModules()
    {
        return $this->hasMany(Module::class)
            ->where('is_published', true)
            ->orderBy('order');
    }

    public function publishedTopics()
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

    public function enrollments()
    {
        return $this->hasMany(LessonEnrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'lesson_enrollments')
            ->withPivot([
                'enrolled_at',
                'study_pace',
                'study_hours_per_week',
                'target_completion_date',
                'schedule_started_at',
                'schedule_updated_at',
            ])
            ->withTimestamps();
    }

    public function enrolledUsers()
    {
        return $this->students();
    }

    public function questions()
    {
        return $this->hasMany(LessonQuestion::class)->latest();
    }

    public function publishedQuestions()
    {
        return $this->hasMany(LessonQuestion::class)
            ->where('is_published', true)
            ->latest();
    }

    public function getEstimatedDurationLabelAttribute()
    {
        if (! $this->estimated_duration_minutes) {
            return null;
        }

        $hours = intdiv($this->estimated_duration_minutes, 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return $hours . ' saa ' . $minutes . ' dakika';
        }

        if ($hours > 0) {
            return $hours . ' saa';
        }

        return $minutes . ' dakika';
    }

    public function getRecommendedStudyPaceLabelAttribute()
    {
        return match ($this->recommended_study_pace) {
            'relaxed' => 'Taratibu',
            'regular' => 'Kawaida',
            'intensive' => 'Haraka',
            'custom' => 'Ratiba Maalum',
            default => null,
        };
    }

    public function getCourseDeadlineLabelAttribute()
    {
        if (! $this->course_deadline) {
            return null;
        }

        return $this->course_deadline->format('d M Y');
    }

    public function getHasTimelineRulesAttribute()
    {
        return filled($this->estimated_duration_minutes)
            || filled($this->recommended_study_pace)
            || filled($this->min_completion_days)
            || filled($this->max_completion_days)
            || filled($this->course_deadline)
            || filled($this->reminder_days_before_deadline);
    }
}