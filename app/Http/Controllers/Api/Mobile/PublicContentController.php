<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Devotion;
use App\Models\Lesson;
use App\Models\Testimonial;
use App\Models\WatotoVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicContentController extends Controller
{
    /**
     * Convert stored HTML into plain text for native Flutter reading.
     */
    private static function plain(?string $value): string
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
     * Published devotions.
     *
     * GET /api/mobile/v1/devotions
     */
    public function devotions(): JsonResponse
    {
        $today = now('Africa/Dar_es_Salaam')
            ->toDateString();

        $items = Devotion::query()
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', $today)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $items->map(
                static fn (Devotion $item) => [
                    'title' => (string) $item->title,
                    'slug' => (string) $item->slug,
                    'excerpt' => Str::limit(
                        self::plain(
                            $item->feature_text ?: $item->content
                        ),
                        170
                    ),
                    'date' => $item->published_at
                        ?->format('Y-m-d') ?? '',
                    'image_url' => $item->image_url,
                    'url' => route('devotions.show', [
                        'slug' => $item->slug,
                    ]),
                ]
            )->values(),
        ]);
    }

    /**
     * Full published devotion.
     *
     * GET /api/mobile/v1/devotions/{slug}
     */
    public function devotionDetail(string $slug): JsonResponse
    {
        $today = now('Africa/Dar_es_Salaam')
            ->toDateString();

        $item = Devotion::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', $today)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'title' => (string) $item->title,
                'slug' => (string) $item->slug,
                'date' => $item->published_at
                    ?->format('Y-m-d') ?? '',

                'content' => self::plain($item->content),
                'feature_text' => self::plain(
                    $item->feature_text
                ),
                'scripture_reference' => (string) (
                    $item->scripture_reference ?? ''
                ),
                'scripture_text' => self::plain(
                    $item->scripture_text
                ),
                'ellen_white_quote' => self::plain(
                    $item->ellen_white_quote
                ),
                'ellen_white_reference' => (string) (
                    $item->ellen_white_reference ?? ''
                ),
                'lesson' => self::plain($item->lesson),

                'image_url' => $item->image_url,

                'url' => route('devotions.show', [
                    'slug' => $item->slug,
                ]),
            ],
        ]);
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

    /**
     * Published children's content.
     *
     * GET /api/mobile/v1/children
     */
    public function children(): JsonResponse
    {
        $items = WatotoVideo::query()
            ->where('is_published', true)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $items->map(
                static fn (WatotoVideo $item) => [
                    'title' => (string) $item->title,
                    'slug' => (string) $item->slug,
                    'excerpt' => Str::limit(
                        self::plain($item->description),
                        170
                    ),
                    'category' => (string) (
                        $item->category ?? ''
                    ),
                    'date' => $item->created_at
                        ?->format('Y-m-d') ?? '',
                    'thumbnail_url' => $item->youtube_thumbnail,
                    'url' => route('children.show', [
                        'slug' => $item->slug,
                    ]),
                ]
            )->values(),
        ]);
    }

    /**
     * Full published children's content.
     *
     * GET /api/mobile/v1/children/{slug}
     */
    public function childDetail(string $slug): JsonResponse
    {
        $item = WatotoVideo::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $videoId = WatotoVideo::youtubeVideoId(
            $item->youtube_url
        );

        return response()->json([
            'data' => [
                'title' => (string) $item->title,
                'slug' => (string) $item->slug,
                'category' => (string) (
                    $item->category ?? ''
                ),
                'content' => self::plain(
                    $item->description
                ),
                'main_lesson' => self::plain(
                    $item->main_lesson
                ),
                'bible_verse' => self::plain(
                    $item->bible_verse
                ),
                'reflection_question' => self::plain(
                    $item->reflection_question
                ),
                'thumbnail_url' => $item->youtube_thumbnail,
                'youtube_url' => $videoId
                    ? 'https://www.youtube.com/watch?v=' . $videoId
                    : null,
                'date' => $item->created_at
                    ?->format('Y-m-d') ?? '',
                'url' => route('children.show', [
                    'slug' => $item->slug,
                ]),
            ],
        ]);
    }

    /**
     * Approved public testimonies.
     *
     * GET /api/mobile/v1/testimonials
     */
    public function testimonials(): JsonResponse
    {
        $items = Testimonial::query()
            ->where('is_published', true)
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $items->map(
                static fn (Testimonial $item) => [
                    'title' => (string) (
                        $item->title ?: $item->name ?: 'Ushuhuda'
                    ),
                    'excerpt' => Str::limit(
                        self::plain($item->message),
                        170
                    ),
                    'content' => self::plain(
                        $item->message
                    ),
                    'date' => $item->created_at
                        ?->format('Y-m-d') ?? '',
                ]
            )->values(),
        ]);
    }
}
