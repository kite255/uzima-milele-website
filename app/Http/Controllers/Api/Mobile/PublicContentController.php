<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicContentController extends Controller
{
    /**
     * Convert stored HTML into plain text for native Flutter reading.
     */
    public static function plain(?string $value): string
    {
        $text = $value ?? '';

        $text = preg_replace(
            '/<\s*br\s*\/?\s*>/i',
            "\n",
            $text
        );

        $text = preg_replace(
            '/<\s*\/\s*(p|div|li|h[1-6])\s*>/i',
            "\n\n",
            $text
        );

        $text = strip_tags($text);

        $text = html_entity_decode(
            $text,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim($text);
    }

    /**
     * Published Bible lessons.
     *
     * GET /api/mobile/v1/lessons
     */
    public function lessons(): JsonResponse
    {
        $items = Lesson::query()
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $items->map(
                static fn (Lesson $item) => [
                    'title' => (string) $item->title,
                    'slug' => (string) $item->slug,
                    'excerpt' => Str::limit(
                        self::plain($item->description),
                        170
                    ),
                    'category' => (string) (
                        $item->category ?? ''
                    ),
                    'level' => (string) (
                        $item->level ?? ''
                    ),
                    'date' => $item->created_at
                        ?->format('Y-m-d') ?? '',
                    'url' => route('lessons.show', [
                        'lesson' => $item->slug,
                    ]),
                ]
            )->values(),
        ]);
    }

    /**
     * Published Bible lesson overview.
     *
     * GET /api/mobile/v1/lessons/{slug}
     */
    public function lessonDetail(string $slug): JsonResponse
    {
        $item = Lesson::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),

                'modules.topics' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),
            ])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'title' => (string) $item->title,
                'slug' => (string) $item->slug,
                'description' => self::plain(
                    $item->description
                ),
                'content' => self::plain(
                    $item->content ?: $item->description
                ),
                'category' => (string) (
                    $item->category ?? ''
                ),
                'level' => (string) (
                    $item->level ?? ''
                ),
                'date' => $item->created_at
                    ?->format('Y-m-d') ?? '',

                'modules' => $item->modules->map(
                    static fn ($module) => [
                        'title' => (string) $module->title,
                        'description' => self::plain(
                            $module->description
                        ),

                        'topics' => $module->topics->map(
                            static fn ($topic) => [
                                'title' => (string) $topic->title,
                                'slug' => (string) $topic->slug,
                            ]
                        )->values(),
                    ]
                )->values(),

                'url' => route('lessons.show', [
                    'lesson' => $item->slug,
                ]),
            ],
        ]);
    }
}
