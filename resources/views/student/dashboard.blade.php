@extends('layouts.app')

@section('title', 'Dashibodi ya Mwanafunzi')

@section('content')

@php
    use App\Models\Lesson;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $authUser = auth()->user();

    $dashboardProgress = min(100, max(0, (int) ($progressPercent ?? $overallProgress ?? 0)));
    $certificateCount = isset($certificates) ? $certificates->count() : 0;
    $attemptsCount = $quizAttempts ?? $totalAttempts ?? 0;

    $paceLabels = [
        Lesson::PACE_RELAXED => 'Taratibu',
        Lesson::PACE_REGULAR => 'Kawaida',
        Lesson::PACE_INTENSIVE => 'Haraka',
        Lesson::PACE_CUSTOM => 'Ratiba Maalum',
    ];
@endphp

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        {{-- WELCOME --}}
        <div class="relative overflow-hidden mb-10 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Dashibodi ya Mwanafunzi
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Karibu, {{ $authUser->name ?? 'Mwanafunzi' }}
                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Endelea kujifunza, fuatilia maendeleo yako, kamilisha masomo na upate vyeti vyako.
                </p>
            </div>

            <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="absolute right-32 top-8 w-24 h-24 rounded-full bg-white/10"></div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-6 py-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 px-6 py-4 font-bold">
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-6 py-4 font-bold">
                {{ session('error') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Masomo Yaliyokamilika</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $completedLessons ?? 0 }}/{{ $totalLessons ?? 0 }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Maendeleo</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    {{ $dashboardProgress }}%
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Majaribio Yaliyofanyika</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $attemptsCount }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Vyeti</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    {{ $certificateCount }}
                </h2>
            </div>
        </div>

        {{-- OVERALL PROGRESS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-10">
            <div class="flex justify-between mb-3">
                <span class="font-bold text-navy">Maendeleo ya Jumla</span>
                <span class="font-bold text-primary">
                    {{ $dashboardProgress }}%
                </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-primary h-4 rounded-full transition-all duration-500"
                     style="width: {{ $dashboardProgress }}%">
                </div>
            </div>
        </div>

        {{-- LESSONS --}}
        <div class="mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-black text-navy">
                    Endelea Kujifunza
                </h2>

                <a href="{{ route('lessons.index') }}"
                   class="hidden sm:inline-flex text-sm font-bold text-primary hover:text-primaryDark">
                    Tazama Masomo Yote →
                </a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($lessons ?? [] as $lesson)
                    @php
                        $totalTopics = (int) ($lesson->total_topics_count ?? 0);
                        $completedTopics = (int) ($lesson->completed_topics_count ?? 0);
                        $lessonProgress = min(100, max(0, (int) ($lesson->progress ?? 0)));

                        $lessonImage = $lesson->cover_image ?? $lesson->image ?? null;

                        $certificate = $lesson->certificate ?? null;

                        if (! $certificate && isset($certificates)) {
                            if ($certificates instanceof \Illuminate\Support\Collection) {
                                $certificate = $certificates->get($lesson->id);
                            } else {
                                $certificate = $certificates[$lesson->id] ?? null;
                            }
                        }

                        $canGenerateCertificate = $lesson->can_generate_certificate ?? false;

                        $finalQuizRequired = $lesson->final_quiz_required ?? false;
                        $finalQuizPassed = $lesson->final_quiz_passed ?? true;
                        $finalQuiz = $lesson->final_quiz ?? $lesson->finalQuiz ?? null;

                        $nextTopic = $lesson->next_topic ?? null;

                        $enrolledAt = $lesson->pivot?->enrolled_at ?? null;
                        $studyPace = $lesson->pivot?->study_pace ?? null;
                        $studyHoursPerWeek = $lesson->pivot?->study_hours_per_week ?? null;
                        $targetCompletionDate = $lesson->pivot?->target_completion_date ?? null;

                        $studyPaceLabel = $paceLabels[$studyPace] ?? null;

                        $remainingDays = null;
                        $isBehindSchedule = false;
                        $isDueToday = false;
                        $scheduleStatusLabel = 'Hakuna ratiba';
                        $scheduleColor = 'gray';

                        if ($targetCompletionDate) {
                            $targetDate = Carbon::parse($targetCompletionDate)->startOfDay();
                            $today = now()->startOfDay();

                            $remainingDays = $today->diffInDays($targetDate, false);
                            $isBehindSchedule = $today->greaterThan($targetDate);
                            $isDueToday = $today->equalTo($targetDate);

                            if ($isBehindSchedule) {
                                $scheduleStatusLabel = 'Umechelewa';
                                $scheduleColor = 'red';
                            } elseif ($isDueToday) {
                                $scheduleStatusLabel = 'Lengo ni leo';
                                $scheduleColor = 'yellow';
                            } else {
                                $scheduleStatusLabel = 'Unaendelea vizuri';
                                $scheduleColor = 'green';
                            }
                        }

                        $imageUrl = $lessonImage
                            ? Storage::disk('public')->url($lessonImage)
                            : null;
                    @endphp

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg transition overflow-hidden">

                        @if($imageUrl)
                            <img src="{{ $imageUrl }}"
                                 class="w-full h-48 object-cover"
                                 alt="{{ $lesson->title }}">
                        @else
                            <div class="h-48 bg-gradient-to-br from-primary to-navy flex items-center justify-center text-white font-black text-2xl">
                                Uzima Milele
                            </div>
                        @endif

                        <div class="p-6">
                            <h3 class="font-black text-lg text-navy mb-2 leading-snug">
                                {{ $lesson->title }}
                            </h3>

                            @if($enrolledAt)
                                <p class="mb-3 text-xs text-gray-500">
                                    Ulijiunga:
                                    <span class="font-bold text-navy">
                                        {{ Carbon::parse($enrolledAt)->format('d M Y, H:i') }}
                                    </span>
                                </p>
                            @endif

                            {{-- LEARNING SCHEDULE --}}
                            @if($targetCompletionDate)
                                <div class="mb-5 rounded-2xl bg-primary/5 border border-primary/10 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-black text-navy">
                                                Ratiba yako ya kujifunza
                                            </p>

                                            <p class="mt-1 text-xs text-gray-600">
                                                Kasi:
                                                <span class="font-bold text-primary">
                                                    {{ $studyPaceLabel ?? 'Kawaida' }}
                                                    @if($studyHoursPerWeek)
                                                        · {{ $studyHoursPerWeek }} saa/wiki
                                                    @endif
                                                </span>
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black
                                            {{ $scheduleColor === 'red'
                                                ? 'bg-red-100 text-red-700'
                                                : ($scheduleColor === 'yellow'
                                                    ? 'bg-yellow-100 text-yellow-700'
                                                    : 'bg-green-100 text-green-700') }}">
                                            {{ $scheduleStatusLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                        <div class="rounded-xl bg-white border border-primary/10 p-3">
                                            <p class="text-gray-500">Lengo</p>
                                            <p class="font-black text-navy mt-1">
                                                {{ Carbon::parse($targetCompletionDate)->format('d M Y') }}
                                            </p>
                                        </div>

                                        <div class="rounded-xl bg-white border border-primary/10 p-3">
                                            <p class="text-gray-500">
                                                @if($isBehindSchedule)
                                                    Hali
                                                @elseif($isDueToday)
                                                    Lengo
                                                @else
                                                    Siku zilizobaki
                                                @endif
                                            </p>

                                            <p class="font-black mt-1
                                                {{ $isBehindSchedule
                                                    ? 'text-red-600'
                                                    : ($isDueToday ? 'text-yellow-700' : 'text-navy') }}">
                                                @if($isBehindSchedule)
                                                    Pita muda
                                                @elseif($isDueToday)
                                                    Leo
                                                @else
                                                    {{ $remainingDays }} siku
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mb-5 rounded-2xl bg-yellow-50 border border-yellow-200 p-4">
                                    <p class="text-xs font-bold text-yellow-700">
                                        Bado hujaweka ratiba ya kujifunza kwa somo hili.
                                    </p>
                                </div>
                            @endif

                            <p class="text-sm text-gray-500 mb-5 line-clamp-3">
                                {{ Str::limit(strip_tags($lesson->description ?? 'Hakuna maelezo yaliyowekwa.'), 130) }}
                            </p>

                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-bold text-navy">Maendeleo</span>
                                    <span class="font-bold text-navy">{{ $lessonProgress }}%</span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-primary h-3 rounded-full"
                                         style="width: {{ $lessonProgress }}%">
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-400 mb-4">
                                {{ $completedTopics }} / {{ $totalTopics }} mada zimekamilika
                            </p>

                            {{-- CONTINUE / COMPLETE --}}
                            @if($nextTopic)
                                <a href="{{ route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $nextTopic->id]) }}"
                                   class="block text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    {{ $lessonProgress > 0 ? 'Endelea Kusoma' : 'Anza Kusoma' }}
                                </a>
                            @elseif($totalTopics > 0 && $lessonProgress >= 100)
                                @if($finalQuizRequired && ! $finalQuizPassed && $finalQuiz)
                                    <a href="{{ route('quiz.show', $finalQuiz->id) }}"
                                       class="block text-center bg-accent hover:bg-yellow-500 text-navy font-bold py-3 rounded-xl transition">
                                        Fanya Jaribio la Mwisho
                                    </a>
                                @else
                                    <a href="{{ route('lessons.learn', $lesson->slug) }}"
                                       class="block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                        Somo Limekamilika
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('lessons.show', $lesson->slug) }}"
                                   class="block text-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition">
                                    Hakuna Mada
                                </a>
                            @endif

                            {{-- CERTIFICATE ACTIONS --}}
                            @if($certificate)
                                <a href="{{ route('certificates.show', $certificate->certificate_number) }}"
                                   class="mt-3 block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                    Tazama Cheti
                                </a>

                                <a href="{{ route('certificates.download', $certificate->certificate_number) }}"
                                   class="mt-2 block text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    Download Cheti
                                </a>
                            @elseif($lessonProgress < 100)
                                <p class="mt-3 text-xs text-gray-500 text-center">
                                    Kamilisha mada zote ili kupata cheti.
                                </p>
                            @elseif($finalQuizRequired && ! $finalQuizPassed && $finalQuiz)
                                <p class="mt-2 text-xs text-gray-500 text-center">
                                    Lazima ufaulu jaribio la mwisho ili kupata cheti.
                                </p>
                            @elseif($canGenerateCertificate)
                                <form action="{{ route('certificates.issue', $lesson->id) }}" method="POST" class="mt-3">
                                    @csrf

                                    <button type="submit"
                                            class="w-full bg-accent hover:bg-yellow-500 text-navy font-bold py-3 rounded-xl transition">
                                        Tengeneza Cheti
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
                        <h3 class="text-xl font-black text-navy">
                            Bado hujajiunga na somo lolote.
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Fungua orodha ya masomo kisha bonyeza “Jiunge na Somo” ili somo lionekane hapa.
                        </p>

                        <a href="{{ route('lessons.index') }}"
                           class="inline-flex mt-6 bg-primary hover:bg-primaryDark text-white font-bold px-6 py-3 rounded-xl transition">
                            Tazama Masomo
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="sm:hidden mt-6">
                <a href="{{ route('lessons.index') }}"
                   class="block text-center bg-white border border-gray-200 rounded-xl py-3 font-bold text-primary">
                    Tazama Masomo Yote
                </a>
            </div>
        </div>

    </div>
</section>

@endsection