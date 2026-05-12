<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function reminderLogs()
    {
        return $this->hasMany(LessonReminderLog::class, 'user_id', 'user_id')
            ->where('lesson_id', $this->lesson_id);
    }

    public function getStudyPaceLabelAttribute()
    {
        return match ($this->study_pace) {
            'relaxed' => 'Taratibu',
            'regular' => 'Kawaida',
            'intensive' => 'Haraka',
            'custom' => 'Ratiba Maalum',
            default => 'Kawaida',
        };
    }

    public function getRemainingDaysAttribute()
    {
        if (! $this->target_completion_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays(
            $this->target_completion_date->startOfDay(),
            false
        );
    }

    public function getIsBehindScheduleAttribute()
    {
        if (! $this->target_completion_date) {
            return false;
        }

        return now()->greaterThan($this->target_completion_date);
    }
}