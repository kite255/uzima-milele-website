<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'email_send_time',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected $appends = [
        'image_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(
            EmailCampaign::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    */

    public function getImageAttribute($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $path = ltrim((string) $value, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return Storage::disk('public')->exists($path)
            ? $path
            : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Email Scheduling
    |--------------------------------------------------------------------------
    */

    public function effectiveEmailSendTime(): string
    {
        if (filled($this->email_send_time)) {
            return $this->email_send_time;
        }

        return EmailSetting::current()
            ->default_devotion_send_time;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(
        Builder $query
    ): Builder {
        return $query
            ->whereNotNull('published_at')
            ->whereDate(
                'published_at',
                '<=',
                today()
            );
    }

    public function scopeForToday(
        Builder $query
    ): Builder {
        return $query
            ->whereNotNull('published_at')
            ->whereDate(
                'published_at',
                today()
            );
    }
}
