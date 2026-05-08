@extends('layouts.app')

@section('title', 'Masomo ya Biblia')

@php use Illuminate\Support\Str; @endphp

@section('content')

<section class="bg-navy text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-accent font-bold uppercase text-sm">
            Jifunze Biblia
        </p>

        <h1 class="mt-3 text-4xl md:text-5xl font-black">
            Masomo ya Biblia
        </h1>

        <p class="mt-4 text-white/80 max-w-2xl mx-auto">
            Jifunze masomo ya Biblia hatua kwa hatua na ukue kiroho kila siku.
        </p>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 lg:grid-cols-3 gap-8">

        @forelse($lessons as $lesson)

            <a href="{{ route('lessons.show', $lesson->slug) }}"
               class="group bg-white rounded-2xl shadow hover:shadow-xl transition overflow-hidden border border-gray-100">

                @if($lesson->cover_image)
                    <img src="{{ asset('storage/'.$lesson->cover_image) }}"
                         alt="{{ $lesson->title }}"
                         class="w-full h-48 object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="h-48 bg-primary flex items-center justify-center text-white font-bold">
                        Somo la Uzima Milele
                    </div>
                @endif

                <div class="p-6">
                    <span class="inline-flex items-center rounded-full bg-primary/10 text-primary text-xs font-bold px-3 py-1 mb-4">
                        Somo la Biblia
                    </span>

                    <h2 class="text-xl font-black text-navy leading-snug group-hover:text-primary transition">
                        {{ $lesson->title }}
                    </h2>

                    <p class="text-sm text-gray-600 mt-3 leading-relaxed">
                        {{ Str::limit($lesson->description, 120) }}
                    </p>

                    <div class="mt-6 inline-flex items-center text-primary font-bold group-hover:text-primaryDark transition">
                        Tazama Somo →
                    </div>
                </div>
            </a>

        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl p-10 text-center shadow-sm">
                <h2 class="text-xl font-black text-navy">
                    Hakuna masomo yaliyopatikana.
                </h2>

                <p class="text-gray-500 mt-2">
                    Tafadhali rudi tena baadaye.
                </p>
            </div>
        @endforelse

    </div>
</section>

@endsection