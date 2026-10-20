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
    <div class="max-w-7xl mx-auto px-4 grid gap-8 md:grid-cols-2 lg:grid-cols-3 {{ $lessons->count() === 1 ? 'md:grid-cols-1 lg:grid-cols-1 justify-items-center' : '' }}">

        @forelse($lessons as $lesson)

            @php
                $lessonUrl = route('lessons.show', $lesson->slug);
                $scheduleUrl = $lessonUrl . '#learning-schedule';
                $isEnrolled = in_array($lesson->id, $enrolledLessonIds ?? []);

                if (auth()->guest()) {
                    $primaryActionUrl = route('login', [
                        'redirect' => $scheduleUrl,
                    ]);
                    $primaryActionLabel = 'Ingia ili Kuanza →';
                } elseif ($isEnrolled) {
                    $primaryActionUrl = route('lessons.learn', $lesson->slug);
                    $primaryActionLabel = 'Endelea Kujifunza →';
                } else {
                    $primaryActionUrl = $scheduleUrl;
                    $primaryActionLabel = 'Jiunge na Somo →';
                }

                $modulesCount = $lesson->modules_count ?? $lesson->modules()->count();
                $topicsCount = method_exists($lesson, 'topics')
                    ? ($lesson->topics_count ?? $lesson->topics()->count())
                    : null;
            @endphp

            <article class="group w-full max-w-md overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <a href="{{ $lessonUrl }}" class="block overflow-hidden">
                    @if($lesson->cover_image)
                        <img src="{{ asset('storage/'.$lesson->cover_image) }}"
                             alt="{{ $lesson->title }}"
                             class="h-52 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                    @else
                        <div class="flex h-52 items-center justify-center bg-primary text-white font-bold">
                            Somo la Uzima Milele
                        </div>
                    @endif
                </a>

                <div class="p-6">
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                        Somo la Biblia
                    </span>

                    <a href="{{ $lessonUrl }}" class="mt-4 block">
                        <h2 class="text-xl font-black leading-snug text-navy transition group-hover:text-primary">
                            {{ $lesson->title }}
                        </h2>
                    </a>

                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        {{ Str::limit($lesson->description, 150) }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs font-semibold text-gray-500">
                        @if($modulesCount)
                            <span>Moduli {{ $modulesCount }}</span>
                        @endif

                        @if($topicsCount)
                            <span>Mada {{ $topicsCount }}</span>
                        @endif

                        @if($lesson->estimated_duration_label)
                            <span>{{ $lesson->estimated_duration_label }}</span>
                        @endif
                    </div>

                    <div class="mt-6 space-y-3">
                        <a href="{{ $primaryActionUrl }}"
                           class="inline-flex w-full items-center justify-center rounded-xl bg-accent px-5 py-3 text-sm font-black text-navy transition hover:opacity-90">
                            {{ $primaryActionLabel }}
                        </a>

                        <a href="{{ $lessonUrl }}"
                           class="inline-flex w-full items-center justify-center text-sm font-bold text-primary transition hover:text-primaryDark">
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