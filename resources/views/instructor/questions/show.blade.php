@extends('layouts.app')

@section('title', 'Jibu Swali')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4">

        {{-- BACK --}}
        <div class="mb-6">
            <a href="{{ route('instructor.questions.index') }}"
               class="inline-flex items-center rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi kwenye Maswali
            </a>
        </div>

        {{-- HEADER --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-primary">
                        Instructor Q&A
                    </p>

                    <h1 class="mt-2 text-3xl md:text-4xl font-black text-navy">
                        Jibu Swali la Mwanafunzi
                    </h1>

                    <p class="mt-3 text-gray-600 leading-relaxed">
                        Andika jibu fupi, wazi, na lenye kujenga. Jina lako halitaonekana kwa mwanafunzi; ataona jibu kutoka Timu ya Uzima Milele.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($question->status === 'answered' || $question->answer)
                        <span class="rounded-full bg-green-50 text-green-700 border border-green-100 px-4 py-2 text-xs font-black">
                            Answered
                        </span>
                    @else
                        <span class="rounded-full bg-yellow-50 text-yellow-700 border border-yellow-100 px-4 py-2 text-xs font-black">
                            Pending
                        </span>
                    @endif

                    @if(($question->visibility ?? 'private') === 'public')
                        <span class="rounded-full bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2 text-xs font-black">
                            Public
                        </span>
                    @elseif(($question->visibility ?? 'private') === 'hidden')
                        <span class="rounded-full bg-red-50 text-red-700 border border-red-100 px-4 py-2 text-xs font-black">
                            Hidden
                        </span>
                    @else
                        <span class="rounded-full bg-gray-50 text-gray-600 border border-gray-100 px-4 py-2 text-xs font-black">
                            Private
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- QUESTION CARD --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">

            <div class="grid md:grid-cols-3 gap-5 mb-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Somo
                    </p>

                    <p class="mt-2 font-black text-navy leading-snug">
                        {{ $question->lesson->title ?? 'Somo' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Mada
                    </p>

                    <p class="mt-2 font-black text-navy leading-snug">
                        {{ $question->lessonTopic->title ?? 'Swali la jumla' }}
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
                        {{ $question->created_at?->format('d M Y, H:i') }}
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

            @if($question->answer)
                <div class="mt-5 rounded-2xl bg-green-50 border border-green-100 p-5">
                    <p class="text-sm font-black text-green-700">
                        Jibu la Sasa
                    </p>

                    <p class="mt-3 text-gray-700 leading-relaxed">
                        {{ $question->answer }}
                    </p>

                    @if($question->answered_at)
                        <p class="mt-3 text-xs text-gray-500">
                            Ilijibiwa: {{ $question->answered_at->format('d M Y, H:i') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ANSWER FORM --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-2xl font-black text-navy mb-2">
                {{ $question->answer ? 'Hariri Jibu' : 'Andika Jibu' }}
            </h2>

            <p class="text-gray-500 mb-6">
                Chagua kama jibu libaki private au liwe public kwa wanafunzi wengine baada ya kujibiwa.
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
                              rows="7"
                              required
                              placeholder="Andika jibu hapa..."
                              class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-base text-navy placeholder-gray-400 shadow-sm outline-none resize-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">{{ old('answer', $question->answer) }}</textarea>

                    @error('answer')
                        <p class="mt-2 text-sm font-bold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="visibility" class="block text-sm font-black text-navy mb-2">
                        Visibility
                    </label>

                    <select id="visibility"
                            name="visibility"
                            required
                            class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-sm text-navy shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="private" @selected(old('visibility', $question->visibility ?? 'private') === 'private')>
                            Private - linaonekana kwa mwanafunzi aliyeuliza na admin/instructor tu
                        </option>

                        <option value="public" @selected(old('visibility', $question->visibility ?? 'private') === 'public')>
                            Public - linaonekana kwa wanafunzi wengine baada ya kujibiwa
                        </option>

                        <option value="hidden" @selected(old('visibility', $question->visibility ?? 'private') === 'hidden')>
                            Hidden - lifiche kwenye Q&A ya mwanafunzi
                        </option>
                    </select>

                    @error('visibility')
                        <p class="mt-2 text-sm font-bold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-5 rounded-2xl bg-gray-50 border border-gray-100 p-4">
                    <div class="flex items-start gap-3">
                        <input type="checkbox"
                               id="is_published"
                               name="is_published"
                               value="1"
                               class="mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                               @checked(old('is_published', $question->is_published ?? true))>

                        <div>
                            <label for="is_published" class="text-sm font-black text-navy">
                                Ruhusu swali hili lionekane kwenye mfumo
                            </label>

                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                                Kwa kawaida acha hii ikiwa imechaguliwa. Tumia visibility ya Hidden kama hutaki lionekane kwenye orodha ya mwanafunzi.
                            </p>
                        </div>
                    </div>
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