<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'user_name',
        'lesson_topic_id',
        'score',
        'correct',
        'total',
        'passed',
    ];

    protected $casts = [
        'score' => 'integer',
        'correct' => 'integer',
        'total' => 'integer',
        'passed' => 'boolean',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }
}