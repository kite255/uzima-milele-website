<?php

namespace App\Http\Controllers;

use App\Models\Devotion;

class DevotionController extends Controller
{
    public function index()
    {
        $today = now('Africa/Dar_es_Salaam')->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Today’s featured devotion
        |--------------------------------------------------------------------------
        */
        $featured = Devotion::query()
            ->whereDate('published_at', $today)
            ->latest('published_at')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Show only today and past devotions
        |--------------------------------------------------------------------------
        */
        $devotions = Devotion::query()
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', $today)
            ->when($featured, function ($query) use ($featured) {
                $query->where('id', '!=', $featured->id);
            })
            ->latest('published_at')
            ->paginate(9);

        return view('devotions.index', compact('devotions', 'featured'));
    }

    public function show($slug)
    {
        $today = now('Africa/Dar_es_Salaam')->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Block future devotions from direct URL access
        |--------------------------------------------------------------------------
        */
        $devotion = Devotion::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', $today)
            ->firstOrFail();

        return view('devotions.show', compact('devotion'));
    }
}