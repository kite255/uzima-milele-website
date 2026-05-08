<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatotoQuiz extends Model
{
    protected $fillable = [
        'watoto_video_id',
        'question',
        'type',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'explanation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function video()
    {
        return $this->belongsTo(WatotoVideo::class, 'watoto_video_id');
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'mcq';
    }
}