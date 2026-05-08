<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonQuestion extends Model
{
    protected $fillable = [
        'lesson_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'is_published',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}