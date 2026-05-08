@extends('layouts.app')

@section('title', 'Jibu Swali')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4">

        {{-- BACK --}}
        <div class="mb-6">
            <a href="{{ route('instructor.questions.index') }}"
               class="inline-flex rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi kwenye Maswali
            </a>
        </div>

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg mb-8">
            <p class="text-white/80 text-sm font-bold mb-2">
                Instructor Q&A
            </p>

            <h1 class="text-3xl md:text-4xl font-black">
                Jibu Swali la Mwanafunzi
            </h1>

            <p class="text-white/85 mt-3">
                Andika jibu fupi, wazi, na lenye kujenga kiroho.
            </p>
        </div>

        {{-- QUESTION CARD --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <div class="flex flex-wrap items-center gap-2 mb-5">
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

            <div class="grid md:grid-cols-2 gap-5 mb-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Somo
                    </p>

                    <p class="mt-2 font-black text-navy">
                        {{ $question->lesson->title ?? 'Somo' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Mwanafunzi
                    </p>

                    <p class="mt-2 font-black text-navy">
                        {{ $question->user->name ?? 'Mwanafunzi' }}
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $question->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl bg-primary/5 border border-primary/10 p-5">
                <p class="text-sm font-black text-primary">
                    Swali la Mwanafunzi
                </p>

                <p class="mt-3 text-lg text-navy leading-relaxed font-bold">
                    {{ $question->question }}
                </p>
            </div>
        </div>

        {{-- ANSWER FORM --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-2xl font-black text-navy mb-2">
                {{ $question->answer ? 'Hariri Jibu' : 'Andika Jibu' }}
            </h2>

            <p class="text-gray-500 mb-6">
                Jibu hili litaonekana kwenye ukurasa wa somo kwa wanafunzi.
            </p>

            <form method="POST" action="{{ route('instructor.questions.update', $question) }}">
                @csrf
                @method('PUT')

                <div>
                    <label for="answer" class="block text-sm font-black text-navy mb-2">
                        Jibu
                    </label>

                    <textarea id="answer"
                              name="answer"
                              rows="8"
                              required
                              placeholder="Andika jibu hapa..."
                              class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-base text-navy placeholder-gray-400 shadow-sm outline-none resize-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">{{ old('answer', $question->answer) }}</textarea>

                    @error('answer')
                        <p class="mt-2 text-sm font-bold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-5 flex items-center gap-3">
                    <input type="checkbox"
                           id="is_published"
                           name="is_published"
                           value="1"
                           class="rounded border-gray-300 text-primary focus:ring-primary"
                           @checked(old('is_published', $question->is_published))>

                    <label for="is_published" class="text-sm font-bold text-navy">
                        Onyesha swali na jibu kwenye ukurasa wa somo
                    </label>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="{{ route('instructor.questions.index') }}"
                       class="inline-flex justify-center rounded-xl bg-gray-100 px-6 py-3 text-navy font-bold hover:bg-gray-200 transition">
                        Ghairi
                    </a>

                    <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-primary px-8 py-3 text-white font-black hover:bg-primaryDark transition">
                        Hifadhi Jibu
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

@endsection