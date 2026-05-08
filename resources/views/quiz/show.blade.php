@extends('layouts.app')

@section('title', $quiz->title)

@section('content')

@php
    $result = session('result');
    $review = session('review', []);

    $lesson = $quiz->lesson
        ?? $quiz->module?->lesson
        ?? $quiz->topic?->module?->lesson
        ?? null;

    $quizTypeLabel = $quiz->quiz_type === 'kupimwa'
        ? 'Jaribio la Kupimwa'
        : 'Jaribio la Kujipima';

    $requiredLabel = $quiz->is_required
        ? 'Lazima kufaulu'
        : 'Hiari';

    $requiredClass = $quiz->is_required
        ? 'bg-red-50 text-red-700 border-red-200'
        : 'bg-blue-50 text-blue-700 border-blue-200';

    $typeClass = $quiz->quiz_type === 'kupimwa'
        ? 'bg-amber-50 text-amber-700 border-amber-200'
        : 'bg-green-50 text-green-700 border-green-200';
@endphp

<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow p-6 md:p-8">

            <div class="mb-8">
                <a href="{{ $lesson ? route('lessons.show', $lesson->slug) : route('student.dashboard') }}"
                   class="text-sm text-primary font-bold hover:underline">
                    ← Rudi kwenye somo
                </a>

                <h1 class="mt-4 text-3xl md:text-4xl font-black text-navy">
                    {{ $quiz->title }}
                </h1>

                @if($quiz->description)
                    <p class="mt-3 text-gray-600 leading-7">
                        {{ $quiz->description }}
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold {{ $typeClass }}">
                        {{ $quizTypeLabel }}
                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold {{ $requiredClass }}">
                        {{ $requiredLabel }}
                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold bg-gray-50 text-gray-700 border-gray-200">
                        Alama ya kufaulu: {{ $quiz->pass_mark }}%
                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold bg-gray-50 text-gray-700 border-gray-200">
                        Maswali: {{ $quiz->questions->count() }}
                    </span>
                </div>

                <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 leading-6">
                    @if($quiz->quiz_type === 'kujipima')
                        Jaribio hili ni la kujipima. Linakusaidia kupima uelewa wako, lakini halizuii kuendelea na somo.
                    @else
                        Jaribio hili ni la kupimwa. Unahitaji kupata angalau {{ $quiz->pass_mark }}% ili kufaulu.
                    @endif

                    @if($quiz->is_required)
                        <br>
                        <strong>Angalizo:</strong> Mwalimu ameweka jaribio hili kuwa la lazima kufaulu.
                    @endif
                </div>
            </div>

            @if(session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            {{-- RESULT --}}
            @if($result)
                <div class="mb-8 p-5 rounded-xl border
                    {{ $result['passed'] ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">

                    <p class="font-black text-xl">
                        Alama: {{ $result['score'] }}%
                    </p>

                    <p class="mt-1">
                        Majibu sahihi: {{ $result['correct'] }} / {{ $result['total'] }}
                    </p>

                    <p class="mt-2 font-semibold">
                        {{ $result['passed'] ? 'Hongera! Umefaulu jaribio hili.' : 'Hujafaulu bado. Unaweza kurudia tena.' }}
                    </p>

                    <p class="mt-2 text-sm">
                        Alama ya kufaulu: {{ $result['pass_mark'] ?? $quiz->pass_mark }}%
                    </p>
                </div>
            @endif

            {{-- QUIZ FORM --}}
            @if(!$result)
                <form method="POST" action="{{ route('quiz.submit', $quiz->id) }}">
                    @csrf

                    @foreach($quiz->questions as $index => $question)
                        <div class="mb-8 rounded-2xl border border-gray-200 p-5">

                            <p class="font-bold text-navy mb-4 leading-7">
                                {{ $index + 1 }}. {{ $question->question }}
                            </p>

                            @error('question_' . $question->id)
                                <p class="mb-3 text-sm text-red-600 font-semibold">
                                    {{ $message }}
                                </p>
                            @enderror

                            @if($question->type === 'true_false')
                                <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                    <input
                                        type="radio"
                                        name="question_{{ $question->id }}"
                                        value="true"
                                        required
                                        class="text-primary focus:ring-primary"
                                    >
                                    <span class="text-sm text-gray-700">Kweli</span>
                                </label>

                                <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                    <input
                                        type="radio"
                                        name="question_{{ $question->id }}"
                                        value="false"
                                        required
                                        class="text-primary focus:ring-primary"
                                    >
                                    <span class="text-sm text-gray-700">Siyo kweli</span>
                                </label>
                            @else
                                @php
                                    $options = $question->options;

                                    if (is_string($options)) {
                                        $decodedOptions = json_decode($options, true);
                                        $options = json_last_error() === JSON_ERROR_NONE ? $decodedOptions : [];
                                    }

                                    $options = is_array($options) ? $options : [];
                                @endphp

                                @foreach($options as $optionIndex => $option)
                                    @php
                                        $optionText = is_array($option)
                                            ? ($option['text'] ?? $option['label'] ?? json_encode($option))
                                            : $option;
                                    @endphp

                                    <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                        <input
                                            type="radio"
                                            name="question_{{ $question->id }}"
                                            value="{{ $optionIndex }}"
                                            required
                                            class="text-primary focus:ring-primary"
                                        >

                                        <span class="text-sm text-gray-700">
                                            {{ $optionText }}
                                        </span>
                                    </label>
                                @endforeach
                            @endif

                        </div>
                    @endforeach

                    <button type="submit"
                            class="w-full bg-primary hover:bg-primaryDark text-white font-bold py-4 rounded-xl transition">
                        Wasilisha Majibu
                    </button>
                </form>
            @endif

            {{-- REVIEW --}}
            @if($result && count($review))
                <div class="mt-8">
                    <h2 class="text-2xl font-black text-navy mb-4">
                        Mapitio ya Majibu
                    </h2>

                    @foreach($quiz->questions as $index => $question)
                        @php
                            $item = $review[$question->id] ?? null;

                            $options = $question->options;

                            if (is_string($options)) {
                                $decodedOptions = json_decode($options, true);
                                $options = json_last_error() === JSON_ERROR_NONE ? $decodedOptions : [];
                            }

                            $options = is_array($options) ? $options : [];

                            $userAnswer = $item['user_answer'] ?? null;
                            $correctAnswer = $item['correct_answer'] ?? null;

                            if ($question->type === 'multiple_choice') {
                                $userAnswerText = isset($options[(int) $userAnswer])
                                    ? ($options[(int) $userAnswer]['text'] ?? $options[(int) $userAnswer]['label'] ?? 'Haijulikani')
                                    : 'Haijulikani';

                                $correctAnswerText = isset($options[(int) $correctAnswer])
                                    ? ($options[(int) $correctAnswer]['text'] ?? $options[(int) $correctAnswer]['label'] ?? 'Haijulikani')
                                    : 'Haijulikani';
                            } else {
                                $userAnswerText = $userAnswer === 'true' ? 'Kweli' : 'Siyo kweli';
                                $correctAnswerText = $correctAnswer === 'true' ? 'Kweli' : 'Siyo kweli';
                            }
                        @endphp

                        <div class="mb-4 rounded-xl border p-4 {{ ($item['is_correct'] ?? false) ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                            <p class="font-bold text-navy">
                                {{ $index + 1 }}. {{ $question->question }}
                            </p>

                            <p class="mt-2 text-sm text-gray-700">
                                Jibu lako:
                                <strong>{{ $userAnswerText }}</strong>
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                Jibu sahihi:
                                <strong>{{ $correctAnswerText }}</strong>
                            </p>

                            <p class="mt-2 text-sm font-bold {{ ($item['is_correct'] ?? false) ? 'text-green-700' : 'text-red-700' }}">
                                {{ ($item['is_correct'] ?? false) ? 'Sahihi' : 'Si sahihi' }}
                            </p>

                            @if($question->explanation)
                                <p class="mt-3 text-sm text-gray-700 leading-6">
                                    <strong>Maelezo:</strong> {{ $question->explanation }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ACTION BUTTONS --}}
            @if($result)
                <div class="grid md:grid-cols-3 gap-4 mt-8">

                    <a href="{{ route('quiz.show', $quiz->id) }}"
                       class="text-center bg-gray-100 hover:bg-gray-200 text-navy font-bold py-3 rounded-xl transition">
                        Jaribu Tena
                    </a>

                    @if($lesson)
                        <a href="{{ route('lessons.show', $lesson->slug) }}"
                           class="text-center bg-primary hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                            Endelea Kujifunza
                        </a>
                    @endif

                    <a href="{{ route('student.dashboard') }}"
                       class="text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                        Tazama Dashibodi
                    </a>

                </div>
            @endif

        </div>
    </div>
</section>

@endsection