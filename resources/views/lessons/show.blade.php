@extends('layouts.app')

@section('title', $lesson->title)

@section('content')

@php
    use App\Models\Lesson;
    use Illuminate\Support\Facades\Storage;

    /*
    |--------------------------------------------------------------------------
    | Collections
    |--------------------------------------------------------------------------
    */
    $modules = $lesson->modules ?? collect();

    $topics = $modules
        ->flatMap(fn ($module) => $module->topics ?? collect())
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Counts
    |--------------------------------------------------------------------------
    */
    $totalModules = $modulesCount ?? $modules->count();

    $totalTopics = $totalTopics ?? $topics->count();

    $topicQuestionsCount = $topics
        ->filter(fn ($topic) => $topic->quiz)
        ->flatMap(fn ($topic) => $topic->quiz->questions ?? collect())
        ->count();

    $moduleQuestionsCount = $modules
        ->flatMap(fn ($module) => $module->quizzes ?? collect())
        ->flatMap(fn ($quiz) => $quiz->questions ?? collect())
        ->count();

    $finalQuestionsCount = $lesson->finalQuiz
        ? ($lesson->finalQuiz->questions?->count() ?? 0)
        : 0;

    $totalQuestions = $questionsCount ?? (
        $topicQuestionsCount + $moduleQuestionsCount + $finalQuestionsCount
    );

    /*
    |--------------------------------------------------------------------------
    | Auth / Enrollment
    |--------------------------------------------------------------------------
    */
    $loginUrl = route('login') . '?redirect=' . urlencode(route('lessons.show', $lesson->slug));

    $enrollment = auth()->check()
        ? auth()->user()
            ->lessonEnrollments()
            ->where('lesson_id', $lesson->id)
            ->first()
        : null;

    $studentIsEnrolled = (bool) $enrollment;

    $isEnrolled = $isEnrolled ?? $studentIsEnrolled;

    $completedTopicIds = auth()->check()
        ? \App\Models\LessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->pluck('lesson_topic_id')
            ->unique()
            ->toArray()
        : [];

    $allTopicsCompleted = $totalTopics > 0
        && count(array_intersect($topics->pluck('id')->toArray(), $completedTopicIds)) >= $totalTopics;

    /*
    |--------------------------------------------------------------------------
    | Instructor
    |--------------------------------------------------------------------------
    */
    $instructorName = $lesson->instructor?->name;

    $ministryName = $lesson->instructor?->ministry_name
        ?: 'Uzima Milele Ministry';

    $ministryBio = $lesson->instructor?->ministry_bio
        ?: 'Uzima Milele Ministry hutoa elimu ya Biblia, afya, na jamii kupitia mifumo ya kidijitali kwa lugha ya Kiswahili.';

    $showInstructorName = $instructorName
        && strtolower(trim($instructorName)) !== strtolower(trim($ministryName));

    /*
    |--------------------------------------------------------------------------
    | Cover Image
    |--------------------------------------------------------------------------
    */
    $coverUrl = $lesson->cover_image
        ? Storage::disk('public')->url($lesson->cover_image)
        : null;

    /*
    |--------------------------------------------------------------------------
    | Learning Schedule
    |--------------------------------------------------------------------------
    */
    $ownerEstimatedMinutes = max(1, (int) ($lesson->estimated_duration_minutes ?? 180));
    $ownerEstimatedHours = max(1, (int) ceil($ownerEstimatedMinutes / 60));

    $savedRecommendedPace = $lesson->default_study_pace
        ?? $lesson->recommended_study_pace
        ?? Lesson::PACE_REGULAR;

    /*
    |--------------------------------------------------------------------------
    | Important:
    | If admin selected custom as the recommended pace, we display Regular as
    | the general recommendation because Custom is better as a student choice.
    |--------------------------------------------------------------------------
    */
    $displayRecommendedPace = $savedRecommendedPace === Lesson::PACE_CUSTOM
        ? Lesson::PACE_REGULAR
        : $savedRecommendedPace;

    $relaxedHours = $lesson->getPaceHours(Lesson::PACE_RELAXED);
    $regularHours = $lesson->getPaceHours(Lesson::PACE_REGULAR);
    $intensiveHours = $lesson->getPaceHours(Lesson::PACE_INTENSIVE);

    $relaxedDays = $lesson->calculateCompletionDays(Lesson::PACE_RELAXED);
    $regularDays = $lesson->calculateCompletionDays(Lesson::PACE_REGULAR);
    $intensiveDays = $lesson->calculateCompletionDays(Lesson::PACE_INTENSIVE);

    $relaxedLabel = $lesson->formatCompletionDuration($relaxedDays);
    $regularLabel = $lesson->formatCompletionDuration($regularDays);
    $intensiveLabel = $lesson->formatCompletionDuration($intensiveDays);

    $recommendedHours = $lesson->getPaceHours($displayRecommendedPace);
    $recommendedDays = $lesson->calculateCompletionDays($displayRecommendedPace);
    $recommendedLabel = $lesson->formatCompletionDuration($recommendedDays);

    $paceLabels = [
        Lesson::PACE_RELAXED => 'Taratibu',
        Lesson::PACE_REGULAR => 'Kawaida',
        Lesson::PACE_INTENSIVE => 'Haraka',
        Lesson::PACE_CUSTOM => 'Ratiba Maalum',
    ];

    $recommendedPaceLabel = $paceLabels[$displayRecommendedPace] ?? 'Kawaida';
@endphp

<section class="bg-navy text-white py-14">
    <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">

        <div>
            <a href="{{ route('lessons.index') }}" class="text-white/80 hover:text-white text-sm font-bold">
                ← Rudi kwenye Masomo
            </a>

            <p class="mt-6 text-accent font-bold uppercase text-sm">
                Somo la Biblia
            </p>

            <h1 class="mt-3 text-4xl md:text-5xl font-black leading-tight">
                {{ $lesson->title }}
            </h1>

            @if($lesson->description)
                <p class="mt-5 text-white/80 text-lg leading-relaxed">
                    {{ $lesson->description }}
                </p>
            @endif

            <div class="mt-7 flex flex-wrap gap-3 text-sm">
                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Moduli {{ $totalModules }}
                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Mada {{ $totalTopics }}
                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Maswali {{ $totalQuestions }}
                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Muda {{ $lesson->estimated_duration_label ?? ($ownerEstimatedHours . ' saa') }}
                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Cheti
                </span>
            </div>

            @if($studentIsEnrolled && $enrollment)
                <div class="mt-5 space-y-2 text-sm text-white/80">
                    <p>
                        Ulijiunga tarehe
                        <span class="font-bold text-white">
                            {{ $enrollment->enrolled_at_label }}
                        </span>
                    </p>

                    @if($enrollment->target_completion_date)
                        <p>
                            Ratiba yako:
                            <span class="font-bold text-white">
                                {{ $enrollment->study_pace_label }} · {{ $enrollment->study_hours_label }}
                            </span>
                            · Lengo:
                            <span class="font-bold text-white">
                                {{ $enrollment->target_completion_date_label }}
                            </span>
                        </p>
                    @endif
                </div>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                @auth
                    @if($studentIsEnrolled)
                        <a href="{{ route('lessons.learn', $lesson->slug) }}"
                           class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                            Endelea Kujifunza →
                        </a>

                        <a href="{{ route('student.dashboard') }}"
                           class="inline-flex justify-center rounded-xl bg-white/10 text-white font-bold px-7 py-4 hover:bg-white/20 transition">
                            Nenda Dashboard
                        </a>
                    @else
                        <a href="#learning-schedule"
                           class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                            Chagua Ratiba →
                        </a>
                    @endif
                @else
                    <a href="{{ $loginUrl }}"
                       class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                        Ingia Kuanza Kujifunza →
                    </a>
                @endauth

                <a href="#course-content"
                   class="inline-flex justify-center rounded-xl bg-white/10 text-white font-bold px-7 py-4 hover:bg-white/20 transition">
                    Tazama Yaliyomo
                </a>
            </div>
        </div>

        <div>
            @if($coverUrl)
                <img src="{{ $coverUrl }}"
                     alt="{{ $lesson->title }}"
                     class="w-full h-80 object-cover rounded-3xl shadow-xl">
            @else
                <div class="w-full h-80 bg-primary/20 rounded-3xl shadow-xl flex items-center justify-center font-black text-2xl">
                    Somo la Uzima Milele
                </div>
            @endif
        </div>

    </div>
</section>

<section class="bg-gray-50 py-14">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <main class="lg:col-span-2 space-y-8 min-w-0">

            <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h2 class="text-2xl font-black text-navy">
                    Kuhusu Somo Hili
                </h2>

                <div class="mt-5 prose max-w-none text-gray-700 prose-headings:text-navy prose-a:text-primary">
                    {!! $lesson->content !!}
                </div>
            </div>

            <div id="course-content"
                 x-data="{ openModule: 0 }"
                 class="relative z-0 bg-white rounded-2xl shadow-sm p-6 md:p-8 scroll-mt-32">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-navy">
                            Yaliyomo kwenye Somo
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Fungua kila moduli kuona mada, majaribio, na hatua za kujifunza.
                        </p>
                    </div>

                    <span class="w-fit text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
                        {{ $totalTopics }} Mada
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse($modules as $module)
                        @php
                            $moduleIndex = $loop->index;

                            $moduleQuiz = $module->quiz
                                ?? ($module->quizzes?->first());

                            $moduleTopics = $module->topics ?? collect();
                        @endphp

                        <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">

                            <button type="button"
                                    class="w-full bg-gray-50 px-5 py-4 text-left hover:bg-primary/5 transition"
                                    @click="openModule = openModule === {{ $moduleIndex }} ? null : {{ $moduleIndex }}">

                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-xs font-black text-primary uppercase tracking-wide">
                                            Moduli {{ $moduleIndex + 1 }}
                                        </p>

                                        <h3 class="mt-1 font-black text-navy text-base md:text-lg leading-snug">
                                            {{ $module->title }}
                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $moduleTopics->count() }} Mada
                                        </p>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-3">
                                        @if($moduleQuiz)
                                            <span class="hidden sm:inline-flex text-xs bg-primary/10 text-primary font-bold px-3 py-2 rounded-lg">
                                                Jaribio la Moduli
                                            </span>
                                        @endif

                                        <span class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-black transition-transform duration-300"
                                              :class="openModule === {{ $moduleIndex }} ? 'rotate-180' : ''">
                                            ↓
                                        </span>
                                    </div>
                                </div>
                            </button>

                            <div x-show="openModule === {{ $moduleIndex }}"
                                 x-cloak
                                 class="border-t border-gray-100">

                                <div class="divide-y divide-gray-100">
                                    @forelse($moduleTopics as $topic)
                                        @php
                                            $topicNumber = $topic->order ?: $loop->iteration;

                                            $topicIsCompleted = in_array($topic->id, $completedTopicIds ?? []);

                                            $topicActionUrl = auth()->check()
                                                ? ($studentIsEnrolled
                                                    ? route('lessons.learn', $lesson->slug) . '?topic=' . $topic->id
                                                    : '#learning-schedule')
                                                : $loginUrl;

                                            $topicActionLabel = auth()->check()
                                                ? ($studentIsEnrolled
                                                    ? ($topicIsCompleted ? 'Rudia' : 'Soma')
                                                    : 'Jiunge')
                                                : 'Ingia';
                                        @endphp

                                        <div class="px-5 py-4 hover:bg-gray-50 transition">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                                <div class="flex items-start gap-3 min-w-0">
                                                    <span class="shrink-0 w-8 h-8 rounded-full {{ $topicIsCompleted ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-navy' }} text-xs font-black flex items-center justify-center">
                                                        @if($topicIsCompleted)
                                                            ✓
                                                        @else
                                                            {{ $topicNumber }}
                                                        @endif
                                                    </span>

                                                    <div class="min-w-0">
                                                        <p class="font-bold text-gray-800 leading-snug">
                                                            {{ $topic->title }}
                                                        </p>

                                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                            <span>Mada ya {{ $topicNumber }}</span>

                                                            @if($topic->quiz)
                                                                <span>•</span>
                                                                <span>Maswali {{ $topic->quiz->questions?->count() ?? 0 }}</span>
                                                                <span>•</span>
                                                                <span>Alama ya kufaulu {{ $topic->quiz->pass_mark }}%</span>
                                                                <span>•</span>
                                                                <span>{{ $topic->quiz->is_required ? 'Lazima' : 'Hiari' }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                                    <a href="{{ $topicActionUrl }}"
                                                       class="w-fit text-xs bg-primary text-white font-bold px-4 py-2 rounded-lg hover:bg-primaryDark transition">
                                                        {{ $topicActionLabel }}
                                                    </a>

                                                    @if($topic->quiz)
                                                        @auth
                                                            @if($studentIsEnrolled)
                                                                <a href="{{ route('quiz.show', $topic->quiz->id) }}"
                                                                   class="w-fit text-xs bg-accent text-navy font-bold px-4 py-2 rounded-lg hover:opacity-90 transition">
                                                                    Fanya Jaribio
                                                                </a>
                                                            @else
                                                                <span class="w-fit text-xs bg-gray-100 text-gray-500 font-bold px-4 py-2 rounded-lg">
                                                                    Jiunge kwanza
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="w-fit text-xs bg-gray-100 text-gray-500 font-bold px-4 py-2 rounded-lg">
                                                                Ingia kwanza
                                                            </span>
                                                        @endauth
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-5 py-4 text-gray-500 text-sm">
                                            Hakuna mada zilizoongezwa bado.
                                        </div>
                                    @endforelse
                                </div>

                                @if($moduleQuiz)
                                    <div class="px-5 py-4 bg-primary/5 border-t border-primary/10">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div>
                                                <p class="font-black text-navy">
                                                    Jaribio la Moduli
                                                </p>

                                                <p class="text-sm text-gray-600 mt-1">
                                                    Maswali {{ $moduleQuiz->questions?->count() ?? 0 }}
                                                    · Alama ya kufaulu {{ $moduleQuiz->pass_mark }}%
                                                </p>
                                            </div>

                                            @auth
                                                @if($studentIsEnrolled)
                                                    <a href="{{ route('quiz.show', $moduleQuiz->id) }}"
                                                       class="inline-flex justify-center rounded-xl bg-primary text-white font-bold px-5 py-3 hover:bg-primaryDark transition">
                                                        Fanya Jaribio
                                                    </a>
                                                @else
                                                    <a href="#learning-schedule"
                                                       class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-5 py-3 hover:opacity-90 transition">
                                                        Jiunge Kwanza
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ $loginUrl }}"
                                                   class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-5 py-3 hover:opacity-90 transition">
                                                    Ingia Kwanza
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">
                            Hakuna moduli zilizoongezwa bado.
                        </p>
                    @endforelse
                </div>

                @if($lesson->finalQuiz)
                    <div class="mt-6 rounded-2xl border border-primary/20 bg-primary/5 p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="font-black text-navy text-xl">
                                    Jaribio la Mwisho
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    Maswali {{ $lesson->finalQuiz->questions?->count() ?? 0 }}
                                    · Alama ya kufaulu {{ $lesson->finalQuiz->pass_mark }}%
                                    · {{ $lesson->finalQuiz->is_required ? 'Lazima kwa cheti' : 'Hiari' }}
                                </p>
                            </div>

                            @auth
                                @if($studentIsEnrolled && $allTopicsCompleted)
                                    <a href="{{ route('quiz.show', $lesson->finalQuiz->id) }}"
                                       class="inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                        Fanya Jaribio la Mwisho
                                    </a>
                                @elseif($studentIsEnrolled && ! $allTopicsCompleted)
                                    <span class="inline-flex justify-center rounded-xl bg-gray-100 text-gray-500 font-bold px-6 py-3 cursor-not-allowed">
                                        Kamilisha mada zote kwanza
                                    </span>
                                @else
                                    <a href="#learning-schedule"
                                       class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                                        Jiunge Kwanza
                                    </a>
                                @endif
                            @else
                                <a href="{{ $loginUrl }}"
                                   class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                                    Ingia Kwanza
                                </a>
                            @endauth
                        </div>
                    </div>
                @endif
            </div>

        </main>

        <aside class="space-y-6 h-fit lg:sticky lg:top-32">

            <div id="learning-schedule" class="bg-white rounded-2xl shadow-sm p-6 scroll-mt-32">
                <h3 class="text-xl font-black text-navy">
                    Anza Somo Hili
                </h3>

                <p class="mt-3 text-gray-600">
                    Chagua kasi ya kujifunza. Mfumo utatumia muda wa somo uliowekwa na admin kisha utakupangia lengo la kumaliza.
                </p>

                <div class="mt-5 rounded-2xl bg-primary/5 border border-primary/10 p-4">
                    <p class="text-sm font-black text-navy">
                        Muhtasari wa Ratiba
                    </p>

                    <p class="mt-2 text-sm text-gray-600">
                        Muda wa somo:
                        <span class="font-bold text-primary">
                            {{ $lesson->estimated_duration_label ?? ($ownerEstimatedHours . ' saa') }}
                        </span>
                    </p>

                    <p class="mt-1 text-sm text-gray-600">
                        Ratiba inayopendekezwa:
                        <span class="font-bold text-primary">
                            {{ $recommendedPaceLabel }}
                            · {{ $recommendedHours }} saa kwa wiki
                            · takriban {{ $recommendedLabel }}
                        </span>
                    </p>

    

                    @if($lesson->min_completion_days || $lesson->max_completion_days)
                        <p class="mt-1 text-sm text-gray-600">
                            Mipaka:
                            <span class="font-bold text-navy">
                                @if($lesson->min_completion_days)
                                    angalau siku {{ $lesson->min_completion_days }}
                                @endif

                                @if($lesson->min_completion_days && $lesson->max_completion_days)
                                    ·
                                @endif

                                @if($lesson->max_completion_days)
                                    mwisho siku {{ $lesson->max_completion_days }}
                                @endif
                            </span>
                        </p>
                    @endif

                    @if($lesson->course_deadline)
                        <p class="mt-1 text-sm text-gray-600">
                            Mwisho wa somo:
                            <span class="font-bold text-navy">
                                {{ $lesson->course_deadline_label }}
                            </span>
                        </p>
                    @endif
                </div>

                @if($studentIsEnrolled && $enrollment)
                    <div class="mt-4 rounded-xl border p-4 {{ $enrollment->schedule_status_color === 'red' ? 'bg-red-50 border-red-200' : ($enrollment->schedule_status_color === 'yellow' ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200') }}">
                        <p class="text-sm font-bold {{ $enrollment->schedule_status_color === 'red' ? 'text-red-700' : ($enrollment->schedule_status_color === 'yellow' ? 'text-yellow-700' : 'text-green-700') }}">
                            {{ $enrollment->schedule_status_label }}
                        </p>

                        <p class="text-xs mt-1 {{ $enrollment->schedule_status_color === 'red' ? 'text-red-700' : ($enrollment->schedule_status_color === 'yellow' ? 'text-yellow-700' : 'text-green-700') }}">
                            Umejiunga: {{ $enrollment->enrolled_at_label }}
                        </p>
                    </div>

                    @if($enrollment->target_completion_date)
                        <div class="mt-4 rounded-xl bg-primary/5 border border-primary/10 p-4">
                            <p class="text-sm font-black text-navy">
                                Ratiba yako ya kujifunza
                            </p>

                            <p class="mt-2 text-sm text-gray-600">
                                Kasi:
                                <span class="font-bold text-primary">
                                    {{ $enrollment->study_pace_label }} · {{ $enrollment->study_hours_label }}
                                </span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Lengo:
                                <span class="font-bold text-navy">
                                    {{ $enrollment->target_completion_date_label }}
                                </span>
                            </p>

                            @if(! is_null($enrollment->remaining_days))
                                <p class="mt-1 text-sm {{ $enrollment->is_behind_schedule ? 'text-red-600' : 'text-gray-600' }}">
                                    @if($enrollment->is_behind_schedule)
                                        Umepita muda wa lengo lako.
                                    @else
                                        Siku zilizobaki:
                                        <span class="font-bold">
                                            {{ $enrollment->remaining_days }}
                                        </span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endif
                @endif

                <div class="mt-6 space-y-3">
                    @auth
                        @if($studentIsEnrolled)
                            <a href="{{ route('lessons.learn', $lesson->slug) }}"
                               class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                Endelea Kujifunza
                            </a>

                            <a href="{{ route('student.dashboard') }}"
                               class="w-full inline-flex justify-center rounded-xl bg-gray-100 text-navy font-bold px-6 py-3 hover:bg-gray-200 transition">
                                Nenda Dashboard
                            </a>
                        @else
                            @if($errors->any())
                                <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                                    Tafadhali hakiki taarifa za ratiba kisha ujaribu tena.
                                </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('lessons.enroll', $lesson->slug) }}"
                                  x-data="{ pace: '{{ old('study_pace', $displayRecommendedPace) }}' }"
                                  class="space-y-4">
                                @csrf

                                <div class="space-y-3">

                                    <label class="block cursor-pointer rounded-2xl border p-4 transition"
                                           :class="pace === 'relaxed' ? 'border-primary bg-primary/5' : 'border-gray-200 bg-gray-50 hover:border-primary/40 hover:bg-primary/5'">
                                        <input type="radio"
                                               name="study_pace"
                                               value="{{ Lesson::PACE_RELAXED }}"
                                               x-model="pace"
                                               class="mr-2">

                                        <span class="font-black text-navy">
                                            Taratibu
                                        </span>

                                        <span class="block text-xs text-gray-500 mt-1">
                                            Saa {{ $relaxedHours }} kwa wiki · takriban {{ $relaxedLabel }}
                                        </span>

                                        <span class="block text-xs text-gray-400 mt-1">
                                            Inafaa kwa mwanafunzi mwenye muda mdogo kwa wiki.
                                        </span>
                                    </label>

                                    <label class="block cursor-pointer rounded-2xl border p-4 transition"
                                           :class="pace === 'regular' ? 'border-primary bg-primary/5' : 'border-gray-200 bg-gray-50 hover:border-primary/40 hover:bg-primary/5'">
                                        <input type="radio"
                                               name="study_pace"
                                               value="{{ Lesson::PACE_REGULAR }}"
                                               x-model="pace"
                                               class="mr-2">

                                        <span class="font-black text-navy">
                                            Kawaida
                                        </span>

                                        <span class="block text-xs text-gray-500 mt-1">
                                            Saa {{ $regularHours }} kwa wiki · takriban {{ $regularLabel }}
                                        </span>

                                        <span class="block text-xs text-gray-400 mt-1">
                                            Hii ndiyo ratiba inayopendekezwa kwa wanafunzi wengi.
                                        </span>
                                    </label>

                                    <label class="block cursor-pointer rounded-2xl border p-4 transition"
                                           :class="pace === 'intensive' ? 'border-primary bg-primary/5' : 'border-gray-200 bg-gray-50 hover:border-primary/40 hover:bg-primary/5'">
                                        <input type="radio"
                                               name="study_pace"
                                               value="{{ Lesson::PACE_INTENSIVE }}"
                                               x-model="pace"
                                               class="mr-2">

                                        <span class="font-black text-navy">
                                            Haraka
                                        </span>

                                        <span class="block text-xs text-gray-500 mt-1">
                                            Saa {{ $intensiveHours }} kwa wiki · takriban {{ $intensiveLabel }}
                                        </span>

                                        <span class="block text-xs text-gray-400 mt-1">
                                            Inafaa kama unataka kumaliza somo kwa muda mfupi.
                                        </span>
                                    </label>

                                    <label class="block cursor-pointer rounded-2xl border p-4 transition"
                                           :class="pace === 'custom' ? 'border-primary bg-primary/5' : 'border-gray-200 bg-gray-50 hover:border-primary/40 hover:bg-primary/5'">
                                        <input type="radio"
                                               name="study_pace"
                                               value="{{ Lesson::PACE_CUSTOM }}"
                                               x-model="pace"
                                               class="mr-2">

                                        <span class="font-black text-navy">
                                            Ratiba Maalum
                                        </span>

                                        <span class="block text-xs text-gray-500 mt-1">
                                            Chagua saa zako kwa wiki. Mfumo utahesabu siku za kumaliza kwa kutumia muda wa somo: {{ $lesson->estimated_duration_label ?? ($ownerEstimatedHours . ' saa') }}.
                                        </span>

                                        <div x-show="pace === 'custom'"
                                             x-cloak>
                                            <input type="number"
                                                   name="study_hours_per_week"
                                                   min="1"
                                                   max="40"
                                                   value="{{ old('study_hours_per_week', $regularHours) }}"
                                                   class="mt-3 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary/20"
                                                   placeholder="Mfano: 3">

                                            @error('study_hours_per_week')
                                                <p class="mt-2 text-xs text-red-600">
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </label>

                                    @error('study_pace')
                                        <p class="text-xs text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                    Jiunge na Somo
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ $loginUrl }}"
                           class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                            Ingia Kuanza
                        </a>

                        <a href="{{ route('register') }}"
                           class="w-full inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                            Tengeneza Akaunti
                        </a>
                    @endauth
                </div>

                <div class="mt-6 pt-6 border-t space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moduli</span>
                        <span class="font-bold text-navy">{{ $totalModules }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Mada</span>
                        <span class="font-bold text-navy">{{ $totalTopics }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Maswali</span>
                        <span class="font-bold text-navy">{{ $totalQuestions }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Muda wa Somo</span>
                        <span class="font-bold text-navy">
                            {{ $lesson->estimated_duration_label ?? ($ownerEstimatedHours . ' saa') }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Cheti</span>
                        <span class="font-bold text-navy">Ndiyo</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-xl font-black text-navy">
                    Mwalimu
                </h3>

                <div class="mt-5 flex items-start gap-5">
                    <div class="w-20 h-20 rounded-full bg-transparent border border-primary/20 flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="{{ asset('images/uzima-logo.png') }}"
                             alt="Uzima Milele Ministry"
                             class="w-16 h-16 object-contain">
                    </div>

                    <div class="flex-1 min-w-0">
                        @if($showInstructorName)
                            <h4 class="font-black text-navy text-lg">
                                {{ $instructorName }}
                            </h4>

                            <p class="mt-1 text-sm font-bold text-primary">
                                {{ $ministryName }}
                            </p>
                        @else
                            <h4 class="font-black text-navy text-lg">
                                {{ $ministryName }}
                            </h4>
                        @endif

                        <p class="mt-4 text-sm text-gray-600 leading-relaxed">
                            {{ $ministryBio }}
                        </p>
                    </div>
                </div>
            </div>

        </aside>

    </div>
</section>

@endsection