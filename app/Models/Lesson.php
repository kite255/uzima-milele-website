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
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
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
            ->withPivot('enrolled_at')
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
}