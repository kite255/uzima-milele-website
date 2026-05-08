<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('testimonials', 'public');
        }

        Testimonial::create([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'image' => $imagePath,
            'is_published' => false,
        ]);

        return back()->with(
            'testimony_success',
            'Ushuhuda wako umepokelewa. Utachapishwa baada ya kukaguliwa.'
        );
    }
}