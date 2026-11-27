<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class AppRelease extends Model
{
    protected $fillable = ['version', 'apk_path', 'size_bytes', 'sha256', 'release_notes', 'published_at'];
    protected $casts = ['published_at' => 'datetime', 'size_bytes' => 'integer', 'downloads' => 'integer'];

    public static function current(): ?self
    {
        if (!Schema::hasTable('app_releases')) {
            return null;
        }

        return static::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')->orderByDesc('id')->first();
    }

    public function fileAvailable(): bool
    {
        return preg_match('~^app-releases/[a-f0-9-]{36}\.apk$~D', $this->apk_path) === 1
            && Storage::disk('local')->exists($this->apk_path);
    }
}
