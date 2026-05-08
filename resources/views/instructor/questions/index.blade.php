@extends('layouts.app')

@section('title', 'Instructor Q&A')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="relative overflow-hidden mb-8 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Instructor Q&A
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Maswali ya Wanafunzi
                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Jibu maswali yaliyoulizwa na wanafunzi kwenye masomo yako.
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

        {{-- STATS --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Maswali Yanayosubiri</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $pendingCount }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Maswali Yaliyojibiwa</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    {{ $answeredCount }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Jumla</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    {{ $questions->total() }}
                </h2>
            </div>
        </div>

        {{-- BACK --}}
        <div class="mb-6">
            <a href="{{ route('instructor.dashboard') }}"
               class="inline-flex rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi Dashboard
            </a>
        </div>

        {{-- QUESTIONS LIST --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-black text-navy">
                    Orodha ya Maswali
                </h2>
            </div>

            <div class="divide-y">
                @forelse($questions as $question)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    @if($question->answer)
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Answered
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                            Pending
                                        </span>
                                    @endif

                                    @if($question->is_published)
                                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                            Published
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                            Hidden
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-500">
                                    Somo:
                                    <span class="font-bold text-navy">
                                        {{ $question->lesson->title ?? 'Somo' }}
                                    </span>
                                </p>

                                <p class="text-sm text-gray-500 mt-1">
                                    Mwanafunzi:
                                    <span class="font-bold text-navy">
                                        {{ $question->user->name ?? 'Mwanafunzi' }}
                                    </span>
                                    • {{ $question->created_at->format('d M Y, H:i') }}
                                </p>

                                <h3 class="mt-4 text-lg font-black text-navy">
                                    {{ $question->question }}
                                </h3>

                                @if($question->answer)
                                    <div class="mt-4 rounded-2xl bg-primary/5 border border-primary/10 p-4">
                                        <p class="text-sm font-black text-primary">
                                            Jibu:
                                        </p>

                                        <p class="mt-2 text-gray-700 leading-relaxed">
                                            {{ $question->answer }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="shrink-0">
                                <a href="{{ route('instructor.questions.show', $question) }}"
                                   class="inline-flex justify-center rounded-xl bg-primary px-6 py-3 text-white font-black hover:bg-primaryDark transition">
                                    {{ $question->answer ? 'Hariri Jibu' : 'Jibu Swali' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <h3 class="text-xl font-black text-navy">
                            Hakuna maswali bado.
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Maswali ya wanafunzi yataonekana hapa.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $questions->links() }}
        </div>

    </div>
</section>

@endsection