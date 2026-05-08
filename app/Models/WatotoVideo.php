<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WatotoVideo extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'youtube_url',
        'description',
        'main_lesson',
        'bible_verse',
        'reflection_question',
        'is_featured',
        'is_published',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($video) {
            if (empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });

        static::updating(function ($video) {
            if (empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });
    }

    public function quizzes()
    {
        return $this->hasMany(WatotoQuiz::class);
    }

    public function getYoutubeEmbedAttribute(): string
    {
        $url = $this->youtube_url;

        if (str_contains($url, 'watch?v=')) {
            return str_replace('watch?v=', 'embed/', $url);
        }

        if (str_contains($url, 'youtu.be/')) {
            $id = basename(parse_url($url, PHP_URL_PATH));
            return 'https://www.youtube.com/embed/' . $id;
        }

        return $url;
    }

    public function getYoutubeThumbnailAttribute(): string
    {
        preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]+)/', $this->youtube_url, $matches);

        $videoId = $matches[1] ?? null;

        return $videoId
            ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg"
            : asset('images/placeholder.jpg');
    }
}