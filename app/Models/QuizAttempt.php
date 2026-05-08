<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'correct_answers',
        'total_questions',
        'passed',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'quiz_id' => 'integer',
        'score' => 'integer',
        'correct_answers' => 'integer',
        'total_questions' => 'integer',
        'passed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}