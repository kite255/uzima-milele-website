@extends('layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="relative overflow-hidden mb-10 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Instructor Dashboard
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Karibu, {{ auth()->user()->name }}
                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Fuatilia masomo yako, wanafunzi, maswali ya Q&A, na vyeti vilivyotolewa.
                </p>
            </div>

            <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="absolute right-32 top-8 w-24 h-24 rounded-full bg-white/10"></div>
        </div>

        {{-- STATS --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Masomo Yangu</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $totalLessons }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Wanafunzi</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    {{ $totalStudents }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Maswali Mapya</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $pendingQuestions }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Yaliyojibiwa</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    {{ $answeredQuestions }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-navy">
                <p class="text-sm text-gray-500">Vyeti</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $certificatesIssued }}
                </h2>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-10">
            <h2 class="text-2xl font-black text-navy mb-5">
                Quick Actions
            </h2>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('instructor.questions.index') }}"
                   class="rounded-2xl bg-accent/20 text-navy font-black p-5 hover:bg-accent transition">
                    Answer Q&A
                </a>

                <a href="{{ route('lessons.index') }}"
                   class="rounded-2xl bg-gray-100 text-navy font-black p-5 hover:bg-gray-200 transition">
                    View Public Lessons
                </a>

                <a href="{{ route('instructor.dashboard') }}"
                   class="rounded-2xl bg-primary/10 text-primary font-black p-5 hover:bg-primary hover:text-white transition">
                    Refresh Dashboard
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ url('/admin') }}"
                       class="rounded-2xl bg-navy text-white font-black p-5 hover:bg-primaryDark transition">
                        Open Admin Panel
                    </a>
                @endif
            </div>
        </div>

        {{-- LESSONS TABLE --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-black text-navy">
                    Masomo Yangu
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="px-6 py-4">Somo</th>
                            <th class="px-6 py-4">Modules</th>
                            <th class="px-6 py-4">Topics</th>
                            <th class="px-6 py-4">Students</th>
                            <th class="px-6 py-4">Questions</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($lessons as $lesson)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-black text-navy">
                                        {{ $lesson->title }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $lesson->category ?? 'No category' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    {{ $lesson->modules_count }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $lesson->topics_count }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $lesson->enrollments_count }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $lesson->questions_count }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($lesson->is_published)
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Published
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('lessons.show', $lesson->slug) }}"
                                           class="text-primary font-bold hover:underline">
                                            View
                                        </a>

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ url('/admin/lessons/' . $lesson->id . '/edit') }}"
                                               class="text-navy font-bold hover:underline">
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                    Huna masomo yoyote bado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RECENT QUESTIONS --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-2xl font-black text-navy">
                    Maswali ya Hivi Karibuni
                </h2>

                <a href="{{ route('instructor.questions.index') }}"
                   class="text-primary font-bold hover:underline">
                    Answer Questions →
                </a>
            </div>

            <div class="divide-y">
                @forelse($recentQuestions as $question)
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <p class="font-black text-navy">
                                    {{ $question->user->name ?? 'Mwanafunzi' }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $question->lesson->title ?? 'Somo' }} • {{ $question->created_at->format('d M Y, H:i') }}
                                </p>

                                <p class="mt-3 text-gray-700">
                                    {{ $question->question }}
                                </p>
                            </div>

                            <div class="shrink-0 flex flex-col gap-2 items-start md:items-end">
                                @if($question->answer)
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                        Answered
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                        Pending
                                    </span>
                                @endif

                                <a href="{{ route('instructor.questions.show', $question) }}"
                                   class="text-primary font-bold hover:underline text-sm">
                                    {{ $question->answer ? 'Hariri Jibu' : 'Jibu Swali' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-500">
                        Hakuna maswali bado.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>

@endsection