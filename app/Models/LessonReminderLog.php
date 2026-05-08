<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonReminderLog extends Model
{
    protected $fillable = [
        'lesson_id',
        'user_id',
        'reminder_day',
        'mode',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}