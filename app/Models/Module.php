<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'order',
        'is_published',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_published' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function topics()
    {
        return $this->hasMany(LessonTopic::class, 'module_id')
            ->orderBy('order');
    }

    public function publishedTopics()
    {
        return $this->hasMany(LessonTopic::class, 'module_id')
            ->where('is_published', true)
            ->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function publishedQuiz()
    {
        return $this->hasOne(Quiz::class)
            ->where('is_published', true);
    }
}