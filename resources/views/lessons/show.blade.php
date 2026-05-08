@extends('layouts.app')

@section('title', $lesson->title)

@section('content')

@php
    $totalModules = $modulesCount ?? $lesson->modules->count();

    $totalTopics = $totalTopics ?? $lesson->modules
        ->flatMap(fn ($module) => $module->topics)
        ->count();

    $topicQuestionsCount = $lesson->modules
        ->flatMap(fn ($module) => $module->topics)
        ->filter(fn ($topic) => $topic->quiz)
        ->flatMap(fn ($topic) => $topic->quiz->questions)
        ->count();

    $moduleQuestionsCount = $lesson->modules
        ->flatMap(fn ($module) => $module->quizzes ?? collect())
        ->flatMap(fn ($quiz) => $quiz->questions)
        ->count();

    $finalQuestionsCount = $lesson->finalQuiz
        ? $lesson->finalQuiz->questions->count()
        : 0;

    $totalQuestions = $questionsCount ?? ($topicQuestionsCount + $moduleQuestionsCount + $finalQuestionsCount);

    $loginUrl = route('login') . '?redirect=' . urlencode(route('lessons.show', $lesson));

    $instructorName = $lesson->instructor?->name;
    $ministryName = $lesson->instructor?->ministry_name ?: 'Uzima Milele Ministry';
    $ministryBio = $lesson->instructor?->ministry_bio
        ?: 'Uzima Milele Ministry hutoa elimu ya Biblia, afya, na jamii kupitia mifumo ya kidijitali kwa lugha ya Kiswahili.';

    $showInstructorName = $instructorName
        && strtolower(trim($instructorName)) !== strtolower(trim($ministryName));

    $studentIsEnrolled = auth()->check()
        ? auth()->user()->lessonEnrollments()->where('lesson_id', $lesson->id)->exists()
        : false;

    $isEnrolled = $isEnrolled ?? $studentIsEnrolled;

    $enrollment = auth()->check()
        ? auth()->user()->lessonEnrollments()->where('lesson_id', $lesson->id)->first()
        : null;

    $completedTopicIds = auth()->check()
        ? \App\Models\LessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->pluck('lesson_topic_id')
            ->unique()
            ->toArray()
        : [];

    $allTopicsCompleted = ($totalTopics ?? 0) > 0
        && count($completedTopicIds) >= ($totalTopics ?? 0);
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
                    Cheti
                </span>
            </div>

            @if($studentIsEnrolled && $enrollment)
                <p class="mt-5 text-sm text-white/80">
                    Ulijiunga tarehe
                    <span class="font-bold text-white">
                        {{ optional($enrollment->enrolled_at)->format('d M Y, H:i') }}
                    </span>
                </p>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                @auth
                    @if($studentIsEnrolled)
                        <a href="{{ route('lessons.learn', $lesson) }}"
                           class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                            Endelea Kujifunza →
                        </a>

                        <a href="{{ route('student.dashboard') }}"
                           class="inline-flex justify-center rounded-xl bg-white/10 text-white font-bold px-7 py-4 hover:bg-white/20 transition">
                            Nenda Dashboard
                        </a>
                    @else
                        <form method="POST" action="{{ route('lessons.enroll', $lesson) }}">
                            @csrf

                            <button type="submit"
                                    class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                                Jiunge na Somo →
                            </button>
                        </form>
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
            @if($lesson->cover_image)
                <img src="{{ asset('storage/' . $lesson->cover_image) }}"
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

            <div id="course-content" class="relative z-0 bg-white rounded-2xl shadow-sm p-6 md:p-8 scroll-mt-32">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h2 class="text-2xl font-black text-navy">
                        Yaliyomo kwenye Somo
                    </h2>

                    <span class="w-fit text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
                        Mada {{ $totalTopics }}
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse($lesson->modules as $module)
                        @php
                            $moduleQuiz = $module->quiz ?? $module->quizzes?->first();
                        @endphp

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-black text-navy">
                                            {{ $module->title }}
                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Mada {{ $module->topics->count() }}
                                        </p>
                                    </div>

                                    @if($moduleQuiz)
                                        @auth
                                            @if($studentIsEnrolled)
                                                <a href="{{ route('quiz.show', $moduleQuiz->id) }}"
                                                   class="shrink-0 text-xs bg-primary text-white font-bold px-3 py-2 rounded-lg hover:bg-primaryDark transition">
                                                    Jaribio la Moduli
                                                </a>
                                            @else
                                                <span class="shrink-0 text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                    Jiunge kwanza
                                                </span>
                                            @endif
                                        @else
                                            <span class="shrink-0 text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                Ingia kwanza
                                            </span>
                                        @endauth
                                    @endif
                                </div>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @forelse($module->topics as $topic)
                                    <div class="px-5 py-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div>
                                                <p class="font-bold text-gray-800">
                                                    {{ $topic->order }}. {{ $topic->title }}
                                                </p>

                                                @if($topic->quiz)
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        Maswali {{ $topic->quiz->questions->count() }}
                                                        · Alama ya kufaulu {{ $topic->quiz->pass_mark }}%
                                                        · {{ $topic->quiz->is_required ? 'Lazima' : 'Hiari' }}
                                                    </p>
                                                @endif
                                            </div>

                                            @if($topic->quiz)
                                                @auth
                                                    @if($studentIsEnrolled)
                                                        <a href="{{ route('quiz.show', $topic->quiz->id) }}"
                                                           class="w-fit text-xs bg-accent text-navy font-bold px-3 py-2 rounded-lg hover:opacity-90 transition">
                                                            Fanya Jaribio
                                                        </a>
                                                    @else
                                                        <span class="w-fit text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                            Jiunge kwanza
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="w-fit text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                        Ingia kwanza
                                                    </span>
                                                @endauth
                                            @else
                                                <span class="w-fit text-xs bg-gray-100 text-gray-500 font-bold px-3 py-1 rounded-full">
                                                    Mada
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-5 py-4 text-gray-500 text-sm">
                                        Hakuna mada zilizoongezwa bado.
                                    </div>
                                @endforelse
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
                                    Maswali {{ $lesson->finalQuiz->questions->count() }}
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
                                    <form method="POST" action="{{ route('lessons.enroll', $lesson) }}">
                                        @csrf

                                        <button type="submit"
                                                class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                                            Jiunge Kwanza
                                        </button>
                                    </form>
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

            {{-- COURSE DISCUSSION / Q&A --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-navy">
                            Maswali na Majibu
                        </h2>

                        <p class="mt-2 text-gray-600">
                            Uliza swali kuhusu somo hili au soma majibu yaliyotolewa.
                        </p>
                    </div>

                    <span class="w-fit shrink-0 text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
                        {{ $lesson->publishedQuestions->count() }} Maswali
                    </span>
                </div>

                {{-- ASK QUESTION FORM --}}
                @auth
                    @if($isEnrolled)
                        <form method="POST"
                              action="{{ route('lessons.questions.store', $lesson->slug) }}"
                              class="mb-8 rounded-3xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-6 md:p-8 shadow-sm">
                            @csrf

                            <div class="mb-4">
                                <label for="question" class="block text-lg font-black text-navy">
                                    Uliza Swali
                                </label>

                                <p class="mt-1 text-sm text-gray-500">
                                    Andika swali lako kwa uwazi ili mwalimu aweze kujibu vizuri.
                                </p>
                            </div>

                            <div>
                                <textarea id="question"
                                          name="question"
                                          rows="5"
                                          required
                                          placeholder="Mfano: Je, maana ya maombi ya kweli ni nini katika maisha ya Mkristo?"
                                          class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-base text-navy placeholder-gray-400 shadow-sm outline-none resize-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">{{ old('question') }}</textarea>

                                @error('question')
                                    <p class="mt-2 text-sm font-bold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <p class="text-xs text-gray-500">
                                    Swali lako litaonekana kwenye sehemu ya Maswali na Majibu.
                                </p>

                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-2xl bg-primary px-8 py-3.5 text-white font-black shadow-md transition hover:bg-primaryDark hover:shadow-lg w-full sm:w-auto">
                                    Tuma Swali
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mb-8 rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
                            <p class="text-yellow-700 font-bold">
                                Tafadhali jiunge na somo hili kwanza ili uweze kuuliza swali.
                            </p>
                        </div>
                    @endif
                @else
                    <div class="mb-8 rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
                        <p class="text-yellow-700 font-bold">
                            Tafadhali ingia kwenye akaunti yako ili uweze kuuliza swali.
                        </p>

                        <a href="{{ route('login') }}"
                           class="mt-3 inline-flex rounded-xl bg-primary px-5 py-2 text-white font-bold hover:bg-primaryDark transition">
                            Ingia Sasa
                        </a>
                    </div>
                @endauth

                {{-- QUESTIONS LIST --}}
                <div class="space-y-5">
                    @forelse($lesson->publishedQuestions as $question)
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center font-black shrink-0">
                                    {{ strtoupper(substr($question->user->name ?? 'M', 0, 1)) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <p class="font-black text-navy">
                                            {{ $question->user->name ?? 'Mwanafunzi' }}
                                        </p>

                                        <p class="text-xs text-gray-400">
                                            {{ $question->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>

                                    <p class="mt-3 text-gray-700 leading-relaxed">
                                        {{ $question->question }}
                                    </p>

                                    @if($question->answer)
                                        <div class="mt-5 rounded-2xl bg-primary/5 border border-primary/10 p-5">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <p class="font-black text-primary">
                                                    Jibu la Mwalimu
                                                </p>

                                                @if($question->answered_at)
                                                    <p class="text-xs text-gray-400">
                                                        {{ $question->answered_at->format('d M Y, H:i') }}
                                                    </p>
                                                @endif
                                            </div>

                                            <p class="mt-3 text-gray-700 leading-relaxed">
                                                {{ $question->answer }}
                                            </p>

                                            @if($question->answeredBy)
                                                <p class="mt-3 text-xs text-gray-500">
                                                    Imejibiwa na:
                                                    <span class="font-bold text-navy">
                                                        Mwalimu wa Uzima Milele
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-4 inline-flex rounded-full bg-gray-100 px-4 py-2 text-xs font-bold text-gray-500">
                                            Bado halijajibiwa
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 p-8 text-center">
                            <h3 class="text-lg font-black text-navy">
                                Hakuna maswali bado.
                            </h3>

                            <p class="mt-2 text-gray-500">
                                Kuwa wa kwanza kuuliza swali kuhusu somo hili.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

        </main>

        <aside class="space-y-6 h-fit lg:sticky lg:top-32">

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-xl font-black text-navy">
                    Anza Somo Hili
                </h3>

                <p class="mt-3 text-gray-600">
                    Jiunge na somo ili lijitokeze kwenye dashibodi yako na uweze kufuatilia maendeleo yako.
                </p>

                @if($studentIsEnrolled && $enrollment)
                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 p-4">
                        <p class="text-sm font-bold text-green-700">
                            Umejiunga na somo hili
                        </p>

                        <p class="text-xs text-green-700 mt-1">
                            {{ optional($enrollment->enrolled_at)->format('d M Y, H:i') }}
                        </p>
                    </div>
                @endif

                <div class="mt-6 space-y-3">
                    @auth
                        @if($studentIsEnrolled)
                            <a href="{{ route('lessons.learn', $lesson) }}"
                               class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                Endelea Kujifunza
                            </a>

                            <a href="{{ route('student.dashboard') }}"
                               class="w-full inline-flex justify-center rounded-xl bg-gray-100 text-navy font-bold px-6 py-3 hover:bg-gray-200 transition">
                                Nenda Dashboard
                            </a>
                        @else
                            <form method="POST" action="{{ route('lessons.enroll', $lesson) }}">
                                @csrf

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