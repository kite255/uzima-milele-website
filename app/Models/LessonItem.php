<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonItem extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'video_url',
        'notes',
        'order',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}