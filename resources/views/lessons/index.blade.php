@extends('layouts.app')

@section('title', 'Masomo ya Biblia')

@php use Illuminate\Support\Str; @endphp

@section('content')

<section class="bg-navy text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-accent font-bold uppercase text-sm">Jifunze Biblia</p>
        <h1 class="mt-3 text-4xl md:text-5xl font-black">Masomo ya Biblia</h1>
        <p class="mt-4 text-white/80 max-w-2xl mx-auto">
            Jifunze masomo ya Biblia hatua kwa hatua na ukue kiroho kila siku.
        </p>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
        @forelse($lessons as $lesson)
            <a href="{{ route('lessons.show', $lesson->slug) }}"
               class="group flex flex-col h-full min-w-0 overflow-hidden rounded-[20px] bg-white border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 no-underline">
                <div class="relative w-full aspect-[16/9] overflow-hidden bg-slate-100">
                    @if($lesson->cover_image)
                        <img src="{{ asset('storage/'.$lesson->cover_image) }}"
                             alt="{{ $lesson->title }}"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-primary flex items-center justify-center text-white font-bold">
                            Somo la Uzima Milele
                        </div>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-4 sm:p-5 gap-4">
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="font-bold text-sky-700">Somo la Biblia</span>
                        <span class="text-gray-500 text-right">Kozi ya Biblia</span>
                    </div>

                    <h2 class="text-xl font-black leading-snug text-navy group-hover:text-primary transition-colors">
                        {{ $lesson->title }}
                    </h2>

                    <p class="text-sm leading-relaxed text-gray-600 line-clamp-3">
                        {{ Str::limit(strip_tags($lesson->description ?? ''), 160) }}
                    </p>

                    <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 gap-y-2 text-xs text-gray-600">
                        <span class="inline-flex items-center gap-1.5">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253a9 9 0 00-8.25-1.13A2.25 2.25 0 002.25 7.27V19.5a9 9 0 019.75 1.5m0-14.747a9 9 0 018.25-1.13 2.25 2.25 0 011.5 2.147V19.5A9 9 0 0012 21m0-14.747V21"/>
                            </svg>
                            Masomo ya Biblia
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm-6 0v4c3.5 3 8.5 3 12 0v-4M21 9v6"/>
                            </svg>
                            Jifunze kwa kasi yako
                        </span>
                    </div>

                    <span class="flex w-full items-center justify-center gap-3 rounded-xl bg-[#087cb7] px-4 py-3.5 text-white text-sm font-bold group-hover:bg-[#056394] transition-colors">
                        Anza Kujifunza <span aria-hidden="true">→</span>
                    </span>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl p-10 text-center shadow-sm">
                <h2 class="text-xl font-black text-navy">Hakuna masomo yaliyopatikana.</h2>
                <p class="text-gray-500 mt-2">Tafadhali rudi tena baadaye.</p>
            </div>
        @endforelse
    </div>
</section>

@endsection
