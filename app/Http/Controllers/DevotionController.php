<?php

namespace App\Http\Controllers;

use App\Models\Devotion;
use Illuminate\View\View;

class DevotionController extends Controller
{
    /**
     * Display the devotion listing page.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Tanzania time
        |--------------------------------------------------------------------------
        |
        | The devotion date is based on Tanzania local time.
        |
        */

        $now = now('Africa/Dar_es_Salaam');

        $startOfToday = $now->copy()->startOfDay();
        $endOfToday = $now->copy()->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | Today's featured devotion
        |--------------------------------------------------------------------------
        |
        | Only a devotion published today can appear in the featured section.
        |
        | If more than one devotion exists for today, the latest one is used.
        |
        */

        $featured = Devotion::query()
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [
                $startOfToday,
                $endOfToday,
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Published devotion archive
        |--------------------------------------------------------------------------
        |
        | Show:
        |
        | - today's published devotions
        | - previous devotions
        |
        | Do not show:
        |
        | - future devotions
        | - devotion already displayed as featured
        |
        */

        $devotions = Devotion::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)

            ->when(
                $featured,
                fn ($query) => $query->whereKeyNot($featured->getKey())
            )

            ->orderByDesc('published_at')
            ->orderByDesc('id')

            ->paginate(9)
            ->withQueryString();


        return view('devotions.index', [
            'featured' => $featured,
            'devotions' => $devotions,
        ]);
    }


    /**
     * Display an individual devotion.
     */
    public function show(string $slug): View
    {
        /*
        |--------------------------------------------------------------------------
        | Tanzania time
        |--------------------------------------------------------------------------
        */

        $now = now('Africa/Dar_es_Salaam');


        /*
        |--------------------------------------------------------------------------
        | Find published devotion
        |--------------------------------------------------------------------------
        |
        | Future devotions cannot be opened directly using their URL.
        |
        */

        $devotion = Devotion::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->firstOrFail();


        return view('devotions.show', [
            'devotion' => $devotion,
        ]);
    }
}