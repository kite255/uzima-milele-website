<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Devotion extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereDate('published_at', '<=', today());
    }

    public function scopeForToday(Builder $query): Builder
    {
        return $query->whereDate('published_at', today());
    }
}