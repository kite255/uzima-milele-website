@extends('layouts.app')

@section('title', 'Mawasiliano')

@section('content')

<section class="bg-gradient-to-br from-primary via-primaryDark to-navy text-white py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="max-w-3xl">
            <span class="inline-flex px-4 py-2 rounded-full bg-white/15 text-sm font-bold mb-5">
                Wasiliana Nasi
            </span>

            <h1 class="text-4xl md:text-6xl font-black leading-tight">
                Mawasiliano
            </h1>

            <p class="mt-6 text-lg md:text-xl text-white/90 leading-relaxed">
                Tupo tayari kukusikiliza, kukuombea, na kukusaidia kupata taarifa kuhusu huduma za Uzima Milele.
            </p>
        </div>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-8">

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h2 class="text-3xl font-black text-navy mb-4">
                    Tuma Ujumbe
                </h2>

                <p class="text-gray-600 mb-6">
                    Jaza fomu hii kututumia ujumbe wako. Tutakujibu haraka iwezekanavyo.
                </p>

                <form action="#" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Jina lako</label>
                        <input type="text"
                               name="name"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: Neema John">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Email au Simu</label>
                        <input type="text"
                               name="contact"
                               class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                               placeholder="Mfano: email@example.com au 07xx xxx xxx">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Ujumbe</label>
                        <textarea name="message"
                                  rows="5"
                                  class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary"
                                  placeholder="Andika ujumbe wako hapa..."></textarea>
                    </div>

                    <button type="button"
                            class="w-full rounded-full bg-primary hover:bg-primaryDark text-white font-black py-3 transition">
                        Tuma Ujumbe
                    </button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h2 class="text-3xl font-black text-navy mb-4">
                        Taarifa za Mawasiliano
                    </h2>

                    <div class="space-y-5 text-gray-700">
                        <div>
                            <p class="font-black text-navy">Simu</p>
                            <p>+255 764 504 284</p>
                        </div>

                        <div>
                            <p class="font-black text-navy">Email</p>
                            <p>info@uzimamilele.or.tz</p>
                        </div>

                        <div>
                            <p class="font-black text-navy">Website</p>
                            <p>www.uzimamilele.or.tz</p>
                        </div>

                        <div>
                            <p class="font-black text-navy">Mahali</p>
                            <p>Dar es Salaam, Tanzania</p>
                        </div>
                    </div>
                </div>

                <div class="bg-navy rounded-3xl p-6 md:p-8 text-white">
                    <h3 class="text-2xl font-black mb-3">
                        Unahitaji Maombi?
                    </h3>

                    <p class="text-white/80 mb-6">
                        Unaweza kutuma ombi la maombi au kushiriki ushuhuda wako kupitia ukurasa maalum.
                    </p>

                    <a href="{{ route('prayers.testimonies') }}"
                       class="inline-flex px-6 py-3 rounded-full bg-accent text-navy font-black hover:bg-yellow-400 transition">
                        Maombi na Ushuhuda
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection