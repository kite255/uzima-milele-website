<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'lesson_id',
        'module_id',
        'lesson_topic_id',
        'title',
        'quiz_type',
        'description',
        'pass_mark',
        'is_required',
        'is_published',
    ];

    protected $casts = [
        'pass_mark' => 'integer',
        'is_required' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function topic()
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)
            ->orderBy('sort_order');
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isKujipima(): bool
    {
        return $this->quiz_type === 'kujipima';
    }

    public function isKupimwa(): bool
    {
        return $this->quiz_type === 'kupimwa';
    }

    public function isRequired(): bool
    {
        return (bool) $this->is_required;
    }
}