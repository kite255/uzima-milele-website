<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonTopic extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'content',
        'video_url',
        'pdf',
        'order',
        'is_free',
        'is_published',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function lesson()
    {
        return $this->hasOneThrough(
            Lesson::class,
            Module::class,
            'id',
            'id',
            'module_id',
            'lesson_id'
        );
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'lesson_topic_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class, 'lesson_topic_id');
    }

    public function completedByUsers()
    {
        return $this->belongsToMany(User::class, 'lesson_progress', 'lesson_topic_id', 'user_id')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->progress()
            ->where('user_id', $user->id)
            ->exists();
    }
}