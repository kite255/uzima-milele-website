<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'correct_answer',
        'options',
        'explanation',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'options' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ========================
    // RELATIONSHIPS
    // ========================

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // ========================
    // HELPERS (VERY IMPORTANT)
    // ========================

    // Get correct option index for MCQ
    public function getCorrectOptionIndex()
    {
        if ($this->type !== 'multiple_choice') {
            return null;
        }

        return collect($this->options ?? [])
            ->search(fn ($option) => ($option['is_correct'] ?? false) === true);
    }

    // Check if user answer is correct
    public function isCorrect($userAnswer)
    {
        if ($this->type === 'multiple_choice') {
            return (string) $userAnswer === (string) $this->getCorrectOptionIndex();
        }

        if ($this->type === 'true_false') {
            return (string) $userAnswer === (string) $this->correct_answer;
        }

        return false;
    }

    // Scope active questions
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}