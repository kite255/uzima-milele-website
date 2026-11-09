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

    /**
     * Accept only direct YouTube video URLs and return a trusted video ID.
     * A URL that only looks like YouTube must not become an iframe source.
     */
    public static function youtubeVideoId(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $parts = parse_url(trim($url));

        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $id = null;

        if ($host === 'youtu.be') {
            $id = trim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})/?$~', $path, $matches)) {
                $id = $matches[1];
            }
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : null;
    }

    public function getYoutubeEmbedAttribute(): ?string
    {
        $id = self::youtubeVideoId($this->youtube_url);

        // Privacy-enhanced mode reduces some tracking, but is not a substitute
        // for checking the video's child suitability and YouTube data practices.
        return $id ? 'https://www.youtube-nocookie.com/embed/' . $id : null;
    }

    public function getYoutubeThumbnailAttribute(): string
    {
        $id = self::youtubeVideoId($this->youtube_url);

        return $id
            ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg"
            : asset('images/placeholder.jpg');
    }
}
