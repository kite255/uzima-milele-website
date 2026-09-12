<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devotion extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',

        'feature_text',
        'lesson',

        'scripture_reference',
        'scripture_text',

        'ellen_white_quote',
        'ellen_white_reference',

        'image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', today());
    }

    public function scopeForToday(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->whereDate('published_at', today());
    }
}