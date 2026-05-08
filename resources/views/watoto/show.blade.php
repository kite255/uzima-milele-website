@extends('layouts.app')

@section('content')

<section class="relative overflow-hidden bg-[#0E3D4F] text-white py-16 md:py-20">
    <div class="absolute inset-0 bg-gradient-to-r from-[#0E3D4F] via-[#076994] to-[#0083CB]"></div>

    <img src="{{ asset('images/clouds.png') }}" alt="" class="absolute -top-20 -left-20 w-[420px] opacity-20 pointer-events-none">
    <img src="{{ asset('images/clouds2.png') }}" alt="" class="absolute -bottom-24 -right-16 w-[520px] opacity-20 pointer-events-none">

    <div class="relative max-w-6xl mx-auto px-4 text-center">
        <a href="{{ route('children.index') }}"
           class="inline-flex mb-6 bg-white/15 border border-white/20 px-5 py-2 rounded-full font-black hover:bg-white/25 transition">
            ← Rudi Watoto
        </a>

        @if($video->category)
            <span class="inline-block bg-[#F4B122] text-[#0E3D4F] px-5 py-2 rounded-full font-black">
                {{ $video->category }}
            </span>
        @endif

        <h1 class="mt-5 text-4xl md:text-6xl font-black leading-tight">
            {{ $video->title }}
        </h1>

        @if($video->description)
            <p class="mt-5 max-w-3xl mx-auto text-white/90 text-lg leading-relaxed">
                {{ $video->description }}
            </p>
        @endif
    </div>
</section>

<section class="py-12 md:py-16 bg-[#F7FBFD]">
    <div class="max-w-6xl mx-auto px-4">

        <div class="bg-white rounded-[2rem] p-3 shadow-2xl border border-white">
            <iframe
                class="w-full aspect-video rounded-[1.5rem]"
                src="{{ $video->youtube_embed }}"
                title="{{ $video->title }}"
                allowfullscreen>
            </iframe>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mt-10">
            @if($video->main_lesson)
                <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-[#0083CB]/10 flex items-center justify-center mb-4">
                        <span class="font-black text-[#0083CB]">1</span>
                    </div>
                    <h3 class="font-black text-xl text-[#0E3D4F] mb-3">Funzo Kuu</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $video->main_lesson }}</p>
                </div>
            @endif

            @if($video->bible_verse)
                <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-[#F4B122]/20 flex items-center justify-center mb-4">
                        <span class="font-black text-[#0E3D4F]">2</span>
                    </div>
                    <h3 class="font-black text-xl text-[#0E3D4F] mb-3">Mstari wa Biblia</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $video->bible_verse }}</p>
                </div>
            @endif

            @if($video->reflection_question)
                <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-[#0083CB]/10 flex items-center justify-center mb-4">
                        <span class="font-black text-[#0083CB]">3</span>
                    </div>
                    <h3 class="font-black text-xl text-[#0E3D4F] mb-3">Swali la Kutafakari</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $video->reflection_question }}</p>
                </div>
            @endif
        </div>

        {{-- QUIZ SECTION --}}
        @if(isset($questions) && $questions->count())
            <section class="mt-16 relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-50 via-white to-yellow-50 p-6 md:p-10 border border-sky-100 shadow-xl">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#0083CB]/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-[#F4B122]/20 rounded-full blur-2xl"></div>

                <div class="relative z-10">
                    <div class="text-center max-w-2xl mx-auto mb-10">
                        <span class="inline-flex items-center px-5 py-2 rounded-full bg-[#0083CB] text-white text-sm font-black">
                            Jaribio la Watoto
                        </span>

                        <h2 class="mt-5 text-3xl md:text-4xl font-black text-[#0E3D4F]">
                            Umekumbuka Somo?
                        </h2>

                        <p class="mt-3 text-gray-600">
                            Chagua jibu sahihi. Maswali haya yanatoka kwenye somo ulilosoma.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('children.quiz.submit', $video->slug) }}" class="space-y-6">
                        @csrf

                        @foreach($questions as $index => $question)
                            <div class="bg-white rounded-[1.5rem] p-5 md:p-7 shadow-md border border-gray-100">
                                <div class="flex items-start gap-4 mb-5">
                                    <div class="shrink-0 w-11 h-11 rounded-full bg-[#0083CB] text-white flex items-center justify-center font-black shadow">
                                        {{ $index + 1 }}
                                    </div>

                                    <h3 class="text-lg md:text-xl font-black text-[#0E3D4F] leading-snug pt-2">
                                        {{ $question->question }}
                                    </h3>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                                        @if($question->$field)
                                            <label class="group cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="answers[{{ $question->id }}]"
                                                    value="{{ $letter }}"
                                                    required
                                                    class="peer hidden"
                                                >

                                                <div class="flex items-center gap-4 rounded-2xl border-2 border-gray-100 bg-gray-50 p-4 transition
                                                            group-hover:border-[#0083CB]/40 group-hover:bg-sky-50
                                                            peer-checked:border-[#0083CB] peer-checked:bg-[#0083CB] peer-checked:text-white peer-checked:shadow-lg">
                                                    <div class="w-9 h-9 rounded-full bg-white text-[#0083CB] flex items-center justify-center font-black shadow-sm">
                                                        {{ $letter }}
                                                    </div>

                                                    <span class="font-bold">
                                                        {{ $question->$field }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="text-center pt-4">
                            <button type="submit"
                                class="inline-flex items-center justify-center bg-[#F4B122] hover:bg-yellow-500 text-[#0E3D4F] font-black px-10 py-4 rounded-full shadow-lg transition hover:-translate-y-1">
                                Maliza Jaribio
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <div class="mt-10 flex flex-col sm:flex-row gap-4">
            <a href="https://wa.me/?text={{ urlencode($video->title . ' - ' . request()->fullUrl()) }}"
               target="_blank"
               class="inline-flex justify-center bg-[#25D366] text-white font-black px-7 py-3 rounded-full shadow hover:-translate-y-1 transition">
                Share WhatsApp
            </a>

            <a href="{{ route('children.index') }}"
               class="inline-flex justify-center bg-[#F4B122] text-[#0E3D4F] font-black px-7 py-3 rounded-full shadow hover:-translate-y-1 transition">
                ← Rudi Watoto
            </a>
        </div>

    </div>
</section>

@if($relatedVideos->count())
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">

        <div class="mb-8">
            <span class="inline-block bg-[#0083CB]/10 text-[#0083CB] px-5 py-2 rounded-full font-black">
                Endelea Kujifunza
            </span>

            <h2 class="mt-4 text-3xl md:text-4xl font-black text-[#0E3D4F]">
                Video Zinazofanana
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
            @foreach($relatedVideos as $related)
                <article class="group bg-white rounded-[2rem] shadow-lg border border-gray-100 overflow-hidden hover:-translate-y-2 hover:shadow-2xl transition">
                    <a href="{{ route('children.show', $related->slug) }}" class="block overflow-hidden">
                        <img src="{{ $related->youtube_thumbnail }}"
                             alt="{{ $related->title }}"
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-500">
                    </a>

                    <div class="p-6">
                        @if($related->category)
                            <span class="inline-block text-xs font-black bg-[#F4B122]/20 text-[#0E3D4F] px-4 py-1.5 rounded-full mb-3">
                                {{ $related->category }}
                            </span>
                        @endif

                        <h3 class="font-black text-lg text-[#0E3D4F] leading-snug">
                            <a href="{{ route('children.show', $related->slug) }}" class="hover:text-[#0083CB] transition">
                                {{ $related->title }}
                            </a>
                        </h3>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif

@endsection