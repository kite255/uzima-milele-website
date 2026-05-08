<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
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
}