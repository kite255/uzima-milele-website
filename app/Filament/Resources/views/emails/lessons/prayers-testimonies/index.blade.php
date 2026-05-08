@extends('layouts.app')

@section('title', 'Maombi na Ushuhuda')

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-primary via-primaryDark to-navy text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-accent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 -left-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-28">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-4 py-2 rounded-full bg-white/15 text-sm font-bold mb-6">
                Maombi • Ushuhuda • Tumaini
            </span>

            <h1 class="text-4xl md:text-6xl font-black leading-tight">
                Maombi na Ushuhudaa
            </h1>

            <p class="mt-6 text-lg md:text-xl text-white/90 leading-relaxed">
                Tuma ombi lako la maombi au shiriki ushuhuda wa jinsi Mungu alivyokutendea.
                Tunaamini Mungu husikia, hujibu, na hutenda kwa wakati wake.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="#maombi"
                   class="inline-flex justify-center items-center px-6 py-3 rounded-full bg-accent text-navy font-black hover:bg-yellow-400 transition">
                    Tuma Ombi la Maombi
                </a>

                <a href="#ushuhuda"
                   class="inline-flex justify-center items-center px-6 py-3 rounded-full bg-white text-primaryDark font-black hover:bg-gray-100 transition">
                    Shiriki Ushuhuda
                </a>
            </div>
        </div>
    </div>
</section>


{{-- ENCOURAGEMENT --}}
<section class="bg-white py-12">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-blue-50 border-l-4 border-primary rounded-2xl p-6 md:p-8">
            <p class="text-navy text-xl md:text-2xl font-black leading-relaxed">
                “Niite nami nitakuitikia, nami nitakuonyesha mambo makubwa, magumu usiyoyajua.”
            </p>
            <p class="mt-3 text-primaryDark font-bold">
                Yeremia 33:3
            </p>
        </div>
    </div>
</section>


{{-- FORMS --}}
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-8">

            {{-- PRAYER FORM --}}
            <div id="maombi" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="mb-6">
                    <span class="inline-flex px-3 py-1 rounded-full bg-primary/10 text-primary text-sm font-black">
                        Maombi
                    </span>

                    <h2 class="mt-4 text-3xl font-black text-navy">
                        Tuma Ombi la Maombi
                    </h2>

                    <p class="mt-3 text-gray-600">
                        Andika ombi lako. Timu ya Uzima Milele itakuombea kwa upendo na uaminifu.
                    </p>
                </div>

                @if(session('prayer_success'))
                    <div class="mb-5 rounded-xl bg-green-50 border border-green-200 p-4 text-green-700 font-bold">
                        {{ session('prayer_success') }}
                    </div>
                @endif

                <form action="{{ route('prayers.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Jina lako</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: Neema John" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Simu au Email</label>
                        <input type="text" name="contact" value="{{ old('contact') }}"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: 07xx xxx xxx au email@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Aina ya ombi</label>
                        <select name="prayer_type"
                                class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary">
                            <option value="general">Ombi la kawaida</option>
                            <option value="healing">Uponyaji</option>
                            <option value="family">Familia</option>
                            <option value="spiritual">Ukuaji wa kiroho</option>
                            <option value="thanksgiving">Shukrani</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Ombi lako</label>
                        <textarea name="message" rows="5"
                                  class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                                  placeholder="Andika ombi lako hapa..." required>{{ old('message') }}</textarea>
                    </div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_private" value="1"
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-600">
                            Ombi hili liwe la faragha
                        </span>
                    </label>

                    <button type="submit"
                            class="w-full rounded-full bg-primary hover:bg-primaryDark text-white font-black py-3 transition">
                        Tuma Ombi
                    </button>
                </form>
            </div>


            {{-- TESTIMONY FORM --}}
            <div id="ushuhuda" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="mb-6">
                    <span class="inline-flex px-3 py-1 rounded-full bg-accent/20 text-navy text-sm font-black">
                        Ushuhuda
                    </span>

                    <h2 class="mt-4 text-3xl font-black text-navy">
                        Shiriki Ushuhuda Wako
                    </h2>

                    <p class="mt-3 text-gray-600">
                        Eleza kwa kifupi jinsi Mungu alivyokutendea. Ushuhuda wako unaweza kumtia moyo mwingine.
                    </p>
                </div>

                @if(session('testimony_success'))
                    <div class="mb-5 rounded-xl bg-green-50 border border-green-200 p-4 text-green-700 font-bold">
                        {{ session('testimony_success') }}
                    </div>
                @endif

                <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Jina lako</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: Baraka Daniel" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Kichwa cha ushuhuda</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: Mungu ameniponya" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Ushuhuda wako</label>
                        <textarea name="message" rows="5"
                                  class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                                  placeholder="Andika ushuhuda wako hapa..." required>{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Picha yako hiari</label>
                        <input type="file" name="image"
                               class="w-full rounded-xl border border-gray-200 p-3 text-sm">
                    </div>

                    <button type="submit"
                            class="w-full rounded-full bg-accent hover:bg-yellow-400 text-navy font-black py-3 transition">
                        Tuma Ushuhuda
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>


{{-- TESTIMONIES --}}
<section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="max-w-2xl mx-auto text-center mb-12">
            <span class="inline-flex px-4 py-2 rounded-full bg-primary/10 text-primary font-black text-sm">
                Shuhuda
            </span>

            <h2 class="mt-4 text-3xl md:text-4xl font-black text-navy">
                Mungu Anaendelea Kutenda
            </h2>

            <p class="mt-4 text-gray-600">
                Soma shuhuda mbalimbali za watu walioguswa na huduma ya Mungu.
            </p>
        </div>

        @if(isset($testimonials) && $testimonials->count())
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($testimonials as $testimonial)
                    <div class="rounded-3xl border border-gray-100 bg-white shadow-sm p-6 hover:shadow-md transition">
                        <div class="flex items-center gap-4 mb-5">
                            @if($testimonial->image)
                                <img src="{{ asset('storage/' . $testimonial->image) }}"
                                     alt="{{ $testimonial->name }}"
                                     class="w-14 h-14 rounded-full object-cover">
                            @else
                                <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-xl">
                                    {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <h3 class="font-black text-navy">
                                    {{ $testimonial->name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Ushuhuda
                                </p>
                            </div>
                        </div>

                        <h4 class="text-xl font-black text-navy mb-3">
                            {{ $testimonial->title }}
                        </h4>

                        <p class="text-gray-600 leading-relaxed">
                            {{ Str::limit($testimonial->message, 160) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="max-w-2xl mx-auto bg-gray-50 rounded-3xl p-8 text-center">
                <h3 class="text-2xl font-black text-navy">
                    Hakuna ushuhuda uliochapishwa bado.
                </h3>
                <p class="mt-3 text-gray-600">
                    Kuwa wa kwanza kushiriki ushuhuda wa jinsi Mungu alivyokutendea.
                </p>
            </div>
        @endif
    </div>
</section>


{{-- CTA --}}
<section class="bg-navy py-14">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-black text-white">
            Yesu bado anajibu maombi.
        </h2>

        <p class="mt-4 text-white/80 text-lg">
            Usiache kuomba. Mungu anasikia, anaona, na anatenda.
        </p>

        <a href="#maombi"
           class="mt-8 inline-flex px-8 py-3 rounded-full bg-accent text-navy font-black hover:bg-yellow-400 transition">
            Tuma Ombi Sasa
        </a>
    </div>
</section>

@endsection