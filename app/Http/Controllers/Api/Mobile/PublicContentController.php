<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Devotion;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicContentController extends Controller
{
    public function devotions(): JsonResponse
    {
        // Match the website's definition of published content, in Tanzania time.
        $today = now('Africa/Dar_es_Salaam')->toDateString();

        $items = Devotion::query()
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', $today)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'slug', 'title', 'content', 'feature_text', 'published_at']);

        return response()->json([
            'data' => $items->map(static fn (Devotion $item) => [
                'title' => (string) $item->title,
                'excerpt' => Str::limit(trim(html_entity_decode(strip_tags(
                    (string) ($item->feature_text ?: $item->content)
                ), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 170),
                'date' => $item->published_at?->format('Y-m-d') ?? '',
                'url' => route('devotions.show', ['slug' => $item->slug]),
            ])->values(),
        ]);
    }

    public function lessons(): JsonResponse
    {
        $items = Lesson::query()
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'slug', 'title', 'description', 'created_at']);

        return response()->json([
            'data' => $items->map(static fn (Lesson $item) => [
                'title' => (string) $item->title,
                'excerpt' => Str::limit(trim(html_entity_decode(strip_tags(
                    (string) $item->description
                ), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 170),
                'date' => $item->created_at?->format('Y-m-d') ?? '',
                'url' => route('lessons.show', ['lesson' => $item->slug]),
            ])->values(),
        ]);
    }
}
