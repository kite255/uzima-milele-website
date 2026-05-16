@extends('layouts.app')

@section('title', $lesson->title)

@section('content')

@php
    use Illuminate\Support\Facades\Storage;

    $modules = $lesson->modules ?? collect();

    $allTopics = $allTopics ?? $modules
        ->flatMap(fn ($module) => $module->topics ?? collect())
        ->values();

    $completedTopicIds = $completedTopicIds ?? [];

    $totalTopics = $totalTopics ?? $allTopics->count();

    $completedTopicsCount = $completedTopicsCount ?? count($completedTopicIds);

    $progressPercent = $progressPercent ?? (
        $totalTopics > 0
            ? (int) round(($completedTopicsCount / $totalTopics) * 100)
            : 0
    );

    $progressPercent = min(100, max(0, (int) $progressPercent));

    /*
    |--------------------------------------------------------------------------
    | Sequential Topic Unlock Logic
    |--------------------------------------------------------------------------
    */
    $unlockedTopicIds = [];
    $firstIncompleteFound = false;

    foreach ($allTopics as $topicItem) {
        if (! $firstIncompleteFound) {
            $unlockedTopicIds[] = $topicItem->id;
        }

        if (! in_array($topicItem->id, $completedTopicIds, true)) {
            $firstIncompleteFound = true;
        }
    }

    $currentTopicIsLocked = $currentTopic
        ? ! in_array($currentTopic->id, $unlockedTopicIds, true)
        : false;

    if ($currentTopicIsLocked) {
        $currentTopic = $allTopics->first(fn ($topicItem) => in_array($topicItem->id, $unlockedTopicIds, true));
    }

    $currentTopicCompleted = $currentTopic
        ? in_array($currentTopic->id, $completedTopicIds, true)
        : false;

    $currentModuleIndex = $modules->search(function ($module) use ($currentTopic) {
        return $currentTopic && ($module->topics ?? collect())->contains('id', $currentTopic->id);
    });

    $currentModuleIndex = $currentModuleIndex === false ? 0 : $currentModuleIndex;

    $coverUrl = $lesson->cover_image
        ? Storage::disk('public')->url($lesson->cover_image)
        : null;

    $topicPdfUrl = $currentTopic?->pdf
        ? Storage::disk('public')->url($currentTopic->pdf)
        : null;

    $finalQuiz = $finalQuiz ?? $lesson->finalQuiz;

    $allTopicsCompleted = $allTopicsCompleted ?? (
        $totalTopics > 0 && $completedTopicsCount >= $totalTopics
    );

    $statusLabels = [
        'open' => 'Wazi',
        'current' => 'Sasa',
        'locked' => 'Imefungwa',
        'completed' => 'Imekamilika',
    ];
@endphp

<section class="bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- SIDEBAR --}}
        <aside class="relative z-10 space-y-6 h-fit mb-8 lg:mb-0 lg:sticky lg:top-32 order-2 lg:order-1">

            {{-- COURSE CARD --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">

                {{-- SIMPLE HEADER --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-xs font-black text-primary uppercase tracking-wide">
                            Somo
                        </p>

                        <a href="{{ route('lessons.show', $lesson->slug) }}"
                           class="text-xs font-bold text-primary hover:text-primaryDark">
                            Muhtasari
                        </a>
                    </div>

                    <h2 class="mt-2 text-xl font-black text-navy leading-snug">
                        {{ $lesson->title }}
                    </h2>
                </div>

                {{-- PROGRESS --}}
                <div class="mb-6 rounded-2xl bg-primary/5 border border-primary/10 p-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-bold text-navy">Maendeleo</span>
                        <span class="font-black text-primary">{{ $progressPercent }}%</span>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-primary h-3 rounded-full transition-all duration-300"
                             style="width: {{ $progressPercent }}%">
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                        {{ $completedTopicsCount }} / {{ $totalTopics }} mada zimekamilika
                    </p>
                </div>

                {{-- MODULES - PROFESSIONAL DROPDOWN --}}
                <div x-data="{ openModule: {{ (int) $currentModuleIndex }} }" class="space-y-4">
                    @forelse($modules as $module)
                        @php
                            $moduleIndex = $loop->index;

                            $moduleTopics = $module->topics ?? collect();
                            $moduleTopicIds = $moduleTopics->pluck('id')->toArray();

                            $moduleHasUnlockedTopic = count(array_intersect($moduleTopicIds, $unlockedTopicIds)) > 0;

                            $moduleCompleted = count($moduleTopicIds) > 0
                                && count(array_intersect($moduleTopicIds, $completedTopicIds)) >= count($moduleTopicIds);

                            $moduleQuiz = $module->quiz ?? ($module->quizzes?->first());
                            $moduleQuizLocked = ! $moduleCompleted;

                            $moduleLocked = ! $moduleHasUnlockedTopic && ! $moduleCompleted;
                            $moduleCurrent = $currentTopic && $moduleTopics->contains('id', $currentTopic->id);

                            $completedInModule = count(array_intersect($moduleTopicIds, $completedTopicIds));
                            $totalInModule = count($moduleTopicIds);

                            $moduleProgress = $totalInModule > 0
                                ? min(100, round(($completedInModule / $totalInModule) * 100))
                                : 0;
                        @endphp

                        <div class="rounded-2xl border bg-white overflow-hidden transition shadow-sm hover:shadow-md
                            {{ $moduleCurrent ? 'border-primary/30 ring-2 ring-primary/10' : 'border-gray-200' }}
                            {{ $moduleLocked ? 'opacity-75' : '' }}">

                            {{-- MODULE HEADER --}}
                            <button type="button"
                                    class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 hover:bg-gray-50 transition"
                                    @click="openModule = openModule === {{ $moduleIndex }} ? null : {{ $moduleIndex }}">

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-black uppercase tracking-wide text-gray-400">
                                            Moduli {{ $loop->iteration }}
                                        </span>

                                        <span class="text-[11px] text-gray-400">
                                            {{ $completedInModule }}/{{ $totalInModule }} mada
                                        </span>
                                    </div>

                                    <h3 class="mt-1 font-black leading-snug
                                        {{ $moduleLocked ? 'text-gray-400' : 'text-primary' }}">
                                        {{ $module->title }}
                                    </h3>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    @if($moduleCompleted)
                                        <span class="text-[11px] font-black rounded-full bg-green-50 text-green-700 border border-green-100 px-3 py-1">
                                            {{ $statusLabels['completed'] }}
                                        </span>
                                    @elseif($moduleLocked)
                                        <span class="text-[11px] font-black rounded-full bg-gray-100 text-gray-500 border border-gray-200 px-3 py-1">
                                            {{ $statusLabels['locked'] }}
                                        </span>
                                    @elseif($moduleCurrent)
                                        <span class="text-[11px] font-black rounded-full bg-primary/10 text-primary border border-primary/10 px-3 py-1">
                                            {{ $statusLabels['current'] }}
                                        </span>
                                    @else
                                        <span class="text-[11px] font-black rounded-full bg-primary/10 text-primary border border-primary/10 px-3 py-1">
                                            {{ $statusLabels['open'] }}
                                        </span>
                                    @endif

                                    <span class="w-8 h-8 rounded-full border flex items-center justify-center transition-transform duration-200
                                        {{ $moduleLocked ? 'bg-gray-100 text-gray-400 border-gray-200' : 'bg-primary/10 text-primary border-primary/10' }}"
                                          :class="openModule === {{ $moduleIndex }} ? 'rotate-180' : ''">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </button>

                            {{-- MODULE PROGRESS LINE --}}
                            @if($totalInModule > 0)
                                <div class="px-5 pb-4">
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-1.5 bg-primary rounded-full"
                                             style="width: {{ $moduleProgress }}%">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- TOPICS --}}
                            <div x-show="openModule === {{ $moduleIndex }}"
                                 x-cloak
                                 class="border-t border-gray-100 bg-white">

                                <div class="divide-y divide-gray-100">
                                    @forelse($moduleTopics as $topic)
                                        @php
                                            $isActiveTopic = $currentTopic && $currentTopic->id === $topic->id;
                                            $isCompletedTopic = in_array($topic->id, $completedTopicIds, true);
                                            $isUnlockedTopic = in_array($topic->id, $unlockedTopicIds, true);
                                            $isLockedTopic = ! $isUnlockedTopic;
                                        @endphp

                                        @if($isLockedTopic)
                                            <div class="px-5 py-4 text-sm bg-gray-50">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-gray-400 leading-snug">
                                                            {{ $loop->iteration }}. {{ $topic->title }}
                                                        </p>

                                                        <p class="mt-1 text-xs text-gray-400">
                                                            Kamilisha mada iliyotangulia ili kufungua.
                                                        </p>
                                                    </div>

                                                    <span class="shrink-0 text-[11px] font-black rounded-full bg-gray-100 text-gray-500 border border-gray-200 px-3 py-1">
                                                        {{ $statusLabels['locked'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $topic->id]) }}"
                                               class="block px-5 py-4 text-sm transition
                                                    {{ $isActiveTopic ? 'bg-primary/10' : 'hover:bg-gray-50' }}">

                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <p class="font-bold leading-snug {{ $isActiveTopic ? 'text-primary' : 'text-navy' }}">
                                                            {{ $loop->iteration }}. {{ $topic->title }}
                                                        </p>

                                                        @if($isActiveTopic)
                                                            <p class="mt-1 text-xs text-primary">
                                                                Unaendelea kujifunza mada hii.
                                                            </p>
                                                        @elseif($isCompletedTopic)
                                                            <p class="mt-1 text-xs text-green-700">
                                                                Mada hii imekamilika.
                                                            </p>
                                                        @else
                                                            <p class="mt-1 text-xs text-gray-500">
                                                                Mada hii ipo wazi.
                                                            </p>
                                                        @endif
                                                    </div>

                                                    @if($isCompletedTopic)
                                                        <span class="shrink-0 text-[11px] font-black rounded-full bg-green-50 text-green-700 border border-green-100 px-3 py-1">
                                                            {{ $statusLabels['completed'] }}
                                                        </span>
                                                    @elseif($isActiveTopic)
                                                        <span class="shrink-0 text-[11px] font-black rounded-full bg-primary text-white px-3 py-1">
                                                            {{ $statusLabels['current'] }}
                                                        </span>
                                                    @else
                                                        <span class="shrink-0 text-[11px] font-black rounded-full bg-primary/10 text-primary border border-primary/10 px-3 py-1">
                                                            {{ $statusLabels['open'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </a>
                                        @endif
                                    @empty
                                        <p class="text-sm text-gray-500 px-5 py-4">
                                            Hakuna mada kwenye moduli hii.
                                        </p>
                                    @endforelse

                                    @if($moduleQuiz)
                                        <div class="px-5 py-4">
                                            @if($moduleQuizLocked)
                                                <div class="w-full rounded-xl bg-gray-100 text-gray-500 font-bold px-4 py-3 text-sm text-center">
                                                    Jaribio litafunguka baada ya kumaliza mada zote.
                                                </div>
                                            @else
                                                <a href="{{ route('quiz.show', $moduleQuiz->id) }}"
                                                   class="inline-flex w-full justify-center rounded-xl bg-accent text-navy font-bold px-4 py-3 text-sm hover:opacity-90 transition">
                                                    Fanya Jaribio la Moduli
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            Hakuna moduli zilizochapishwa.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- SCHEDULE CARD --}}
            @if($enrollment ?? null)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xl font-black text-navy">
                        Ratiba Yako
                    </h3>

                    <div class="mt-4 rounded-xl border p-4
                        {{ $enrollment->schedule_status_color === 'red'
                            ? 'bg-red-50 border-red-200'
                            : ($enrollment->schedule_status_color === 'yellow'
                                ? 'bg-yellow-50 border-yellow-200'
                                : 'bg-green-50 border-green-200') }}">

                        <p class="text-sm font-black
                            {{ $enrollment->schedule_status_color === 'red'
                                ? 'text-red-700'
                                : ($enrollment->schedule_status_color === 'yellow'
                                    ? 'text-yellow-700'
                                    : 'text-green-700') }}">
                            {{ $enrollment->schedule_status_label }}
                        </p>

                        @if($enrollment->remaining_days_label)
                            <p class="mt-1 text-xs
                                {{ $enrollment->schedule_status_color === 'red'
                                    ? 'text-red-700'
                                    : ($enrollment->schedule_status_color === 'yellow'
                                        ? 'text-yellow-700'
                                        : 'text-green-700') }}">
                                {{ $enrollment->remaining_days_label }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Kasi</span>
                            <span class="font-bold text-navy text-right">
                                {{ $enrollment->study_pace_label }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Muda</span>
                            <span class="font-bold text-navy text-right">
                                {{ $enrollment->study_hours_label ?? '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Lengo</span>
                            <span class="font-bold text-navy text-right">
                                {{ $enrollment->target_completion_date_label ?? '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Ulianza</span>
                            <span class="font-bold text-navy text-right">
                                {{ $enrollment->enrolled_at_label ?? '—' }}
                            </span>
                        </div>
                    </div>

                    @if($enrollment->canResetSchedule())
                        <details class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <summary class="cursor-pointer font-bold text-sm text-primary">
                                Badili Ratiba
                            </summary>

                            <form method="POST"
                                  action="{{ route('lessons.schedule.reset', $lesson->slug) }}"
                                  class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')

                                <select name="study_pace"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary/20">
                                    <option value="{{ \App\Models\Lesson::PACE_RELAXED }}" @selected($enrollment->study_pace === \App\Models\Lesson::PACE_RELAXED)>
                                        Taratibu
                                    </option>
                                    <option value="{{ \App\Models\Lesson::PACE_REGULAR }}" @selected($enrollment->study_pace === \App\Models\Lesson::PACE_REGULAR)>
                                        Kawaida
                                    </option>
                                    <option value="{{ \App\Models\Lesson::PACE_INTENSIVE }}" @selected($enrollment->study_pace === \App\Models\Lesson::PACE_INTENSIVE)>
                                        Haraka
                                    </option>
                                    <option value="{{ \App\Models\Lesson::PACE_CUSTOM }}" @selected($enrollment->study_pace === \App\Models\Lesson::PACE_CUSTOM)>
                                        Ratiba Maalum
                                    </option>
                                </select>

                                <input type="number"
                                       name="study_hours_per_week"
                                       min="1"
                                       max="40"
                                       value="{{ old('study_hours_per_week', $enrollment->study_hours_per_week ?: 3) }}"
                                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary/20"
                                       placeholder="Saa kwa wiki">

                                <button type="submit"
                                        class="w-full rounded-xl bg-primary text-white font-bold px-5 py-3 hover:bg-primaryDark transition">
                                    Hifadhi Ratiba
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            @endif

        </aside>

        {{-- MAIN CONTENT --}}
       <main class="relative z-0 font-lato lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-10 min-w-0 order-1 lg:order-2">

            @if(session('success'))
                <div class="mb-6 rounded-xl bg-green-50 text-green-700 border border-green-200 px-5 py-4 font-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-xl bg-red-50 text-red-700 border border-red-200 px-5 py-4 font-bold">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 text-red-700 border border-red-200 px-5 py-4">
                    <p class="font-bold">
                        Kuna taarifa zinahitaji kurekebishwa:
                    </p>

                    <ul class="mt-2 list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($currentTopic)

                @if($currentTopicIsLocked)
                    <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-6 text-yellow-800">
                        <h2 class="text-2xl font-black">
                            Mada hii imefungwa kwa sasa
                        </h2>

                        <p class="mt-2 text-sm">
                            Tafadhali kamilisha mada iliyotangulia kabla ya kuendelea.
                        </p>
                    </div>
                @else

                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <p class="text-sm text-primary font-bold uppercase tracking-wide">
                                    Mada ya Somo
                                </p>

                                <h2 class="text-3xl md:text-4xl font-black text-navy mt-2 leading-tight">
                                    {{ $currentTopic->title }}
                                </h2>
                            </div>

                            @if($currentTopicCompleted)
                                <span class="w-fit rounded-full bg-green-100 text-green-700 font-black px-4 py-2 text-sm">
                                    Imekamilika
                                </span>
                            @else
                                <span class="w-fit rounded-full bg-primary/10 text-primary font-black px-4 py-2 text-sm">
                                    Unaendelea
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($currentTopic->video_url)
                        @php
                            $videoUrl = $currentTopic->video_url;

                            if (str_contains($videoUrl, 'youtube.com/watch?v=')) {
                                $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                            }

                            if (str_contains($videoUrl, 'youtu.be/')) {
                                $videoId = basename(parse_url($videoUrl, PHP_URL_PATH));
                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                            }

                            if (str_contains($videoUrl, 'youtube.com/live/')) {
                                $path = parse_url($videoUrl, PHP_URL_PATH);
                                $videoId = str_replace('/live/', '', $path);
                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                            }

                            $videoUrl = strtok($videoUrl, '?');
                        @endphp

                        <div class="mb-8 rounded-2xl overflow-hidden shadow-lg border border-gray-200">
                            <div class="aspect-video bg-black">
                                <iframe
                                    class="w-full h-full"
                                    src="{{ $videoUrl }}"
                                    title="{{ $currentTopic->title }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    @elseif($coverUrl)
                        <img src="{{ $coverUrl }}"
                             alt="{{ $lesson->title }}"
                             class="w-full h-72 object-cover rounded-2xl mb-8">
                    @endif

                    <div class="font-lato max-w-none text-gray-700 leading-relaxed space-y-4
                                [&_h2]:text-2xl [&_h2]:font-black [&_h2]:text-navy [&_h2]:mt-6 [&_h2]:mb-3
                                [&_h3]:text-xl [&_h3]:font-black [&_h3]:text-navy [&_h3]:mt-5 [&_h3]:mb-2
                                [&_p]:text-base [&_p]:leading-8 [&_p]:text-gray-700
                                [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6
                                [&_li]:mb-2 [&_a]:text-primary [&_a]:font-bold
                                [&_img]:rounded-2xl [&_img]:shadow-sm">
                        {!! html_entity_decode($currentTopic->content) !!}
                    </div>

                    {{-- TOPIC ACTIONS --}}
                    <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-4">

                        @if(! $currentTopicCompleted)
                            <form method="POST" action="{{ route('lessons.progress', $lesson->slug) }}">
                                @csrf

                                <input type="hidden" name="lesson_topic_id" value="{{ $currentTopic->id }}">

                                <button type="submit"
                                        class="w-full sm:w-auto bg-green-600 text-white font-bold px-6 py-3 rounded-xl shadow hover:bg-green-700 transition">
                                    Nimemaliza Mada Hii
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center justify-center bg-green-50 text-green-700 border border-green-200 font-bold px-6 py-3 rounded-xl text-center">
                                Mada Imekamilika
                            </span>
                        @endif

                        @if($topicPdfUrl)
                            <a href="{{ $topicPdfUrl }}"
                               target="_blank"
                               class="inline-flex items-center justify-center bg-accent text-navy font-bold px-6 py-3 rounded-xl shadow hover:opacity-90 transition text-center">
                                Pakua PDF
                            </a>
                        @endif

                        @if($currentTopic->quiz)
                            <a href="{{ route('quiz.show', $currentTopic->quiz->id) }}"
                               class="inline-flex items-center justify-center bg-primary text-white font-bold px-6 py-3 rounded-xl shadow hover:bg-primaryDark transition text-center">
                                Jibu Maswali
                            </a>
                        @endif
                    </div>

                    {{-- PREVIOUS / NEXT --}}
                    <div class="mt-12 pt-6 border-t flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
                        @if($previousTopic)
                            <a href="{{ route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $previousTopic->id]) }}"
                               class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 hover:text-primary hover:border-primary/40 transition">
                                Iliyopita: {{ $previousTopic->title }}
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if($nextTopic)
                            @php
                                $nextLocked = ! $currentTopicCompleted || ! in_array($nextTopic->id, $unlockedTopicIds, true);
                            @endphp

                            @if($nextLocked)
                                <span class="rounded-xl bg-gray-100 px-5 py-3 text-sm font-bold text-gray-400 text-center cursor-not-allowed">
                                    Kamilisha mada ya sasa ili kufungua inayofuata
                                </span>
                            @else
                                <a href="{{ route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $nextTopic->id]) }}"
                                   class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark transition text-center">
                                    Inayofuata: {{ $nextTopic->title }}
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- FINAL LESSON ACTIONS --}}
                    @if($allTopicsCompleted ?? false)
                        <div class="mt-10 rounded-2xl border border-green-200 bg-green-50 p-6">
                            <h3 class="text-xl font-black text-green-700">
                                Hongera! Umekamilisha mada zote za somo hili.
                            </h3>

                            <p class="mt-2 text-sm text-green-700/80">
                                Hatua inayofuata ni kufanya jaribio la mwisho au kupata cheti cha kukamilisha somo.
                            </p>

                            <div class="mt-5 flex flex-col sm:flex-row flex-wrap gap-3">

                                @if($finalQuiz && ! ($finalQuizPassed ?? false))
                                    <a href="{{ route('quiz.show', $finalQuiz->id) }}"
                                       class="inline-flex items-center justify-center rounded-xl bg-accent px-6 py-3 text-sm font-black text-navy hover:bg-yellow-500 transition">
                                        Fanya Jaribio la Mwisho
                                    </a>
                                @endif

                                @if($certificate ?? null)
                                    <a href="{{ route('certificates.show', $certificate->certificate_number) }}"
                                       class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-black text-white hover:bg-green-700 transition">
                                        Tazama Cheti
                                    </a>

                                    <a href="{{ route('certificates.download', $certificate->certificate_number) }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-green-300 bg-white px-6 py-3 text-sm font-black text-green-700 hover:bg-green-100 transition">
                                        Pakua Cheti
                                    </a>
                                @elseif($canGenerateCertificate ?? false)
                                    <form method="POST" action="{{ route('certificates.issue', $lesson->id) }}">
                                        @csrf

                                        <button type="submit"
                                                class="w-full sm:w-auto rounded-xl bg-accent px-6 py-3 text-sm font-black text-navy hover:bg-yellow-500 transition">
                                            Tengeneza Cheti
                                        </button>
                                    </form>
                                @elseif($finalQuiz && ! ($finalQuizPassed ?? false))
                                    <span class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-500 border border-green-200">
                                        Lazima ufaulu jaribio la mwisho ili kupata cheti.
                                    </span>
                                @endif

                            </div>
                        </div>
                    @endif
                @endif

            @else
                <div class="text-center py-20">
                    <h2 class="text-2xl font-black text-navy">
                        Hakuna mada inayopatikana
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Somo hili bado halina mada zilizochapishwa.
                    </p>

                    <a href="{{ route('lessons.show', $lesson->slug) }}"
                       class="mt-6 inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                        Rudi kwenye Muhtasari
                    </a>
                </div>
            @endif

        </main>

    </div>
</section>

@endsection