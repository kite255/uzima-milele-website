@extends('layouts.app')

@section('content')

@php
    $total = count($results);
    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

    if ($percentage == 100) {
        $message = "Hongera sana! Umefaulu kikamilifu";
        $color = "text-green-600";
    } elseif ($percentage >= 70) {
        $message = "Vizuri sana! Endelea hivyo";
        $color = "text-blue-600";
    } else {
        $message = "Jaribu tena, utaweza!";
        $color = "text-red-600";
    }
@endphp

<div class="bg-gradient-to-br from-sky-50 via-white to-yellow-50 min-h-screen py-12">

    <div class="max-w-5xl mx-auto px-4">

        <div class="text-center mb-10">
            <span class="inline-flex px-5 py-2 rounded-full bg-primary text-white text-sm font-black">
                Matokeo ya Jaribio
            </span>

            <h1 class="mt-5 text-3xl md:text-5xl font-black text-navy">
                Matokeo ya Jaribio
            </h1>

            <p class="text-gray-600 mt-3">
                {{ $video->title }}
            </p>
        </div>

        {{-- SCORE CARD --}}
        <div class="bg-white rounded-[2rem] shadow-xl p-10 text-center mb-10 border border-gray-100 relative overflow-hidden">

            <div class="absolute -top-10 -right-10 w-40 h-40 bg-green-100 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-yellow-100 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <p class="text-lg font-black {{ $color }}">
                    {{ $message }}
                </p>

                <p class="mt-3 text-gray-600 font-bold">
                    Umejibu sahihi
                </p>

                <p class="text-6xl font-black text-primary mt-2">
                    {{ $score }} / {{ $total }}
                </p>

                <p class="mt-3 text-lg font-bold text-navy">
                    {{ $percentage }}%
                </p>

                <div class="mt-6 w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                    <div class="bg-accent h-4 rounded-full transition-all duration-700"
                         style="width: {{ $percentage }}%">
                    </div>
                </div>
            </div>
        </div>

        {{-- RESULTS --}}
        <div class="space-y-6">
            @foreach($results as $index => $item)
                @php
                    $question = $item['question'];

                    $answers = [
                        'A' => $question->option_a,
                        'B' => $question->option_b,
                        'C' => $question->option_c,
                        'D' => $question->option_d,
                    ];

                    $userAnswerText = $answers[$item['user_answer']] ?? 'Hakuna jibu';
                    $correctAnswerText = $answers[$question->correct_answer] ?? $question->correct_answer;
                @endphp

                <div class="bg-white rounded-[1.5rem] shadow-md p-6 border {{ $item['is_correct'] ? 'border-green-200' : 'border-red-200' }}">

                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-full {{ $item['is_correct'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center font-black">
                            {{ $item['is_correct'] ? '✓' : '×' }}
                        </div>

                        <div class="flex-1">
                            <h3 class="font-black text-navy text-lg">
                                {{ $index + 1 }}. {{ $question->question }}
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4 mt-5">
                                <div class="rounded-2xl {{ $item['is_correct'] ? 'bg-green-50' : 'bg-red-50' }} p-4">
                                    <p class="text-sm font-bold text-gray-500">Jibu lako</p>
                                    <p class="mt-1 font-black {{ $item['is_correct'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $item['user_answer'] }}. {{ $userAnswerText }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-green-50 p-4">
                                    <p class="text-sm font-bold text-gray-500">Jibu sahihi</p>
                                    <p class="mt-1 font-black text-green-700">
                                        {{ $question->correct_answer }}. {{ $correctAnswerText }}
                                    </p>
                                </div>
                            </div>

                            @if($question->explanation)
                                <div class="mt-5 bg-sky-50 border border-sky-100 p-4 rounded-2xl">
                                    <p class="font-bold text-primary mb-1">
                                        Maelezo
                                    </p>
                                    <p class="text-gray-700">
                                        {{ $question->explanation }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap justify-center gap-4 mt-10">
            <a href="{{ route('children.show', $video->slug) }}"
               class="bg-accent hover:bg-yellow-500 text-navy font-black px-8 py-4 rounded-full shadow transition">
                Rudia Somo
            </a>

            <a href="{{ route('children.index') }}"
               class="bg-primary hover:bg-primaryDark text-white font-black px-8 py-4 rounded-full shadow transition">
                Masomo Mengine
            </a>
        </div>

    </div>
</div>

{{-- CONFETTI (NO EMOJI, CLEAN EFFECT) --}}
@if($percentage >= 70)
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        confetti({
            particleCount: 120,
            spread: 80,
            origin: { y: 0.6 }
        });

        setTimeout(function () {
            confetti({
                particleCount: 80,
                spread: 100,
                origin: { y: 0.7 }
            });
        }, 600);
    });
</script>
@endif

@endsection