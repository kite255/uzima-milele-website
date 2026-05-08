@extends('layouts.app')

@section('title', 'Maombi na Ushuhuda')

@section('content')

<section class="relative min-h-[calc(100vh-80px)] bg-gray-50">

    {{-- HERO BACKGROUND --}}
    <div class="relative h-[270px] md:h-[340px] bg-cover bg-center"
         style="background-image: url('{{ asset('images/maombi.jpeg') }}');">
        <div class="absolute inset-0 bg-gradient-to-r from-navy/90 via-navy/80 to-primary/60"></div>

        <div class="relative z-10 h-full flex items-center justify-center text-center px-4">
            <div class="max-w-3xl">
               
                <h1 class="text-4xl md:text-5xl font-black text-white leading-tight">
                    Maombi na Ushuhuda
                </h1>

                <p class="mt-4 text-white/90 text-base md:text-lg leading-relaxed">
                    Tuma ombi lako la maombi kwa Uzima Milele. Tupo hapa kukuombea kwa upendo na uaminifu.
                </p>
            </div>
        </div>
    </div>


    {{-- MAIN CARD --}}
    <div class="max-w-3xl mx-auto px-4 -mt-20 relative z-20 pb-16">
        <div class="bg-white rounded-[2rem] shadow-2xl shadow-gray-200/70 border border-gray-100 overflow-hidden">

            {{-- INTRO --}}
            <div class="p-6 md:p-10 border-b border-gray-100 bg-white">
                <div class="flex items-start gap-5">
                    <div class="hidden sm:flex w-14 h-14 rounded-2xl bg-primary/10 items-center justify-center shrink-0">
                        <div class="w-8 h-8 rounded-full border-4 border-primary relative">
                            <div class="absolute inset-1 rounded-full bg-primary/20"></div>
                        </div>
                    </div>

                    <div>
                        <p class="text-primary font-black mb-2">
                            Karibu Uzima Milele
                        </p>

                        <h2 class="text-3xl md:text-4xl font-black text-navy leading-tight mb-4">
                            Mungu Bado Anasikia Maombi
                        </h2>

                        <p class="text-gray-600 leading-8">
                            Tunaamini kuwa maombi yana nguvu. Unaweza kutuma ombi lako kwa faragha,
                            nasi tutakuombea kwa upendo, siri na uaminifu.
                        </p>
                    </div>
                </div>

                <div class="mt-7 rounded-2xl bg-blue-50 border-l-4 border-primary p-5">
                    <p class="text-navy font-bold leading-7">
                        “Niite nami nitakuitikia, nami nitakuonyesha mambo makubwa, magumu usiyoyajua.”
                    </p>
                    <p class="mt-2 text-primaryDark font-black text-sm">
                        Yeremia 33:3
                    </p>
                </div>
            </div>


            {{-- SUCCESS MESSAGE --}}
            @if(session('prayer_success'))
                <div class="px-6 md:px-10 pt-8">
                    <div class="rounded-2xl bg-green-50 border border-green-200 p-4 text-green-700 font-bold">
                        {{ session('prayer_success') }}
                    </div>
                </div>
            @endif


            {{-- PRAYER FORM ONLY --}}
            <div id="maombi" class="p-6 md:p-10">
                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-navy mb-2">
                        Tuma Ombi la Maombi
                    </h2>

                    <p class="text-gray-600 leading-7">
                        Jaza taarifa zako na ombi lako hapa chini. Sehemu zenye alama ya nyota ni lazima kujazwa.
                    </p>
                </div>

                <form action="{{ route('prayers.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-2">
                                Jina la Kwanza <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   placeholder="Jina la Kwanza"
                                   class="w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                                   required>
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-2">
                                Jina la Mwisho <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   placeholder="Jina la Mwisho"
                                   class="w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                                   required>
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">
                            Barua Pepe <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="mfano: jina@email.com"
                               class="w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">
                            Mada <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="subject"
                               value="{{ old('subject') }}"
                               placeholder="Mfano: Ombi la familia"
                               class="w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                               required>
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">
                            Maombi <span class="text-red-500">*</span>
                        </label>
                        <textarea name="message"
                                  rows="6"
                                  placeholder="Andika ombi lako hapa..."
                                  class="w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                                  required>{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl bg-gray-50 border border-gray-100 p-4">
                        <input type="checkbox"
                               name="is_private"
                               value="1"
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-600">
                            Ombi hili liwe la faragha
                        </span>
                    </label>

                    <button type="submit"
                            class="w-full rounded-xl bg-primary hover:bg-primaryDark text-white font-black py-4 transition shadow-sm">
                        Tuma Ombi
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

@endsection