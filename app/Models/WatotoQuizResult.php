<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatotoQuizResult extends Model
{
    protected $fillable = [
        'watoto_video_id',
        'user_name',
        'score',
        'correct',
        'total',
        'passed',
    ];

    protected $casts = [
        'passed' => 'boolean',
    ];

    public function video()
    {
        return $this->belongsTo(WatotoVideo::class, 'watoto_video_id');
    }
}