<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;

class PrayerTestimonyController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::query()
            ->where('is_published', true)
            ->latest()
            ->take(6)
            ->get();

        return view('prayers-testimonies.index', compact('testimonials'));
    }
}