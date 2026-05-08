@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="mb-10">
            <h1 class="text-3xl md:text-4xl font-black text-navy">
                Karibu, {{ auth()->user()->name }}
            </h1>
            <p class="mt-2 text-gray-600">
                Endelea kujifunza na kufuatilia maendeleo yako.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-6 py-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-gray-500">Lessons Completed</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $completedLessons ?? 0 }}/{{ $totalLessons ?? 0 }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-gray-500">Progress</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    {{ $overallProgress ?? 0 }}%
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-gray-500">Quiz Attempts</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    {{ $quizAttempts ?? 0 }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-gray-500">Passed Quizzes</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    {{ $passedQuizzes ?? 0 }}
                </h2>
            </div>
        </div>

        {{-- OVERALL PROGRESS --}}
        <div class="bg-white rounded-2xl shadow p-6 mb-10">
            <div class="flex justify-between mb-3">
                <h2 class="font-bold text-navy">Overall Progress</h2>
                <span class="font-bold text-primary">{{ $overallProgress ?? 0 }}%</span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-primary h-4 rounded-full transition-all duration-700"
                     style="width: {{ $overallProgress ?? 0 }}%">
                </div>
            </div>
        </div>

        {{-- CONTINUE LEARNING --}}
        <div class="mb-12">
            <h2 class="text-2xl font-black text-navy mb-6">
                Continue Learning
            </h2>

            <div class="grid md:grid-cols-3 gap-6">
                @forelse($lessons ?? [] as $lesson)
                    @php
                        $totalTopics = $lesson->modules->flatMap->topics->count();
                        $completedTopics = $lesson->completed_topics_count ?? 0;

                        $lessonProgress = $totalTopics > 0
                            ? round(($completedTopics / $totalTopics) * 100)
                            : 0;

                        $firstTopic = $lesson->modules
                            ->flatMap->topics
                            ->first();

                        $certificate = $lesson->certificate ?? null;
                    @endphp

                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col">
                        <h3 class="text-lg font-black text-navy">
                            {{ $lesson->title }}
                        </h3>

                        <div class="mt-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span>Progress</span>
                                <span>{{ $lessonProgress }}%</span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-primary h-3 rounded-full transition-all duration-700"
                                     style="width: {{ $lessonProgress }}%">
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-3">
                                {{ $completedTopics }} / {{ $totalTopics }} topics completed
                            </p>
                        </div>

                        <div class="mt-auto pt-6 space-y-3">
                            @if($firstTopic)
                                <a href="{{ route('lessons.show', $lesson->slug) }}?topic={{ $firstTopic->id }}"
                                   class="block text-center bg-primary hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    Continue Learning
                                </a>
                            @else
                                <a href="{{ route('lessons.show', $lesson->slug) }}"
                                   class="block text-center bg-primary hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    View Lesson
                                </a>
                            @endif

                            @if($lessonProgress >= 100)
                                @if($certificate)
                                    <a href="{{ route('certificates.show', $certificate->id) }}"
                                       class="block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                        View Certificate
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('certificates.issue', $lesson->id) }}">
                                        @csrf

                                        <button type="submit"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                            Generate Certificate
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="text-center bg-gray-100 text-gray-500 font-bold py-3 rounded-xl text-sm">
                                    Complete lesson to unlock certificate
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow p-8 md:col-span-3">
                        <p class="text-gray-600">No lessons available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RECENT QUIZ RESULTS --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-black text-navy">
                    Recent Quiz Results
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-6 py-4">Quiz</th>
                            <th class="px-6 py-4">Score</th>
                            <th class="px-6 py-4">Correct</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($quizResults ?? [] as $result)
                            <tr class="border-t">
                                <td class="px-6 py-4 font-bold text-navy">
                                    {{ $result->quiz->title ?? 'Quiz' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $result->score }}%
                                </td>

                                <td class="px-6 py-4">
                                    {{ $result->correct_answers }} / {{ $result->total_questions }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($result->passed)
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Passed
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                            Failed
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $result->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-gray-500 text-center">
                                    No quiz results yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

@endsection