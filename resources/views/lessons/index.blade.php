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

            @php
                $lessonUrl = route('lessons.show', $lesson->slug);
                $scheduleUrl = $lessonUrl . '#learning-schedule';
                $isEnrolled = in_array($lesson->id, $enrolledLessonIds ?? []);

                if (auth()->guest()) {
                    $primaryActionUrl = route('login', [
                        'redirect' => $scheduleUrl,
                    ]);
                    $primaryActionLabel = 'Ingia Kuanza Kujifunza →';
                } elseif ($isEnrolled) {
                    $primaryActionUrl = route('lessons.learn', $lesson->slug);
                    $primaryActionLabel = 'Endelea Kujifunza →';
                } else {
                    $primaryActionUrl = $scheduleUrl;
                    $primaryActionLabel = 'Jiunge na Somo →';
                }
            @endphp

            <article class="group bg-white rounded-2xl shadow hover:shadow-xl transition overflow-hidden border border-gray-100">
                <a href="{{ $lessonUrl }}" class="block overflow-hidden">
                    @if($lesson->cover_image)
                        <img src="{{ asset('storage/'.$lesson->cover_image) }}"
                             alt="{{ $lesson->title }}"
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="h-48 bg-primary flex items-center justify-center text-white font-bold">
                            Somo la Uzima Milele
                        </div>
                    @endif
                </a>

                <div class="p-6">
                    <span class="inline-flex items-center rounded-full bg-primary/10 text-primary text-xs font-bold px-3 py-1 mb-4">
                        Somo la Biblia
                    </span>

                    <a href="{{ $lessonUrl }}" class="block">
                        <h2 class="text-xl font-black text-navy leading-snug group-hover:text-primary transition">
                            {{ $lesson->title }}
                        </h2>
                    </a>

                    <p class="text-sm text-gray-600 mt-3 leading-relaxed">
                        {{ Str::limit($lesson->description, 120) }}
                    </p>

                    <div class="mt-6 flex flex-col gap-3">
                        <a href="{{ $primaryActionUrl }}"
                           class="inline-flex items-center justify-center rounded-xl bg-accent px-5 py-3 font-black text-navy transition hover:opacity-90">
                            {{ $primaryActionLabel }}
                        </a>

                        <a href="{{ $lessonUrl }}"
                           class="inline-flex items-center justify-center text-primary font-bold transition hover:text-primaryDark">
                            Tazama Somo →
                        </a>
                    </div>
                </div>
            </article>


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