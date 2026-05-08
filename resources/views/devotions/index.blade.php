@extends('layouts.app')

@section('content')

{{-- BANNER --}}
<section class="relative h-[240px] md:h-[330px] flex items-center justify-center overflow-hidden bg-navy">
    <img src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600&auto=format&fit=crop"
         alt="Tafakari"
         class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-navy/70"></div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-3xl md:text-5xl font-black text-white">Tafakari</h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto">
            Soma tafakari kulingana na tarehe na uendelee kukua kiroho kila siku.
        </p>
    </div>
</section>

{{-- FEATURED DEVOTION --}}
@if($featured)
<section class="bg-white py-12">
    <div class="max-w-6xl mx-auto px-4">

        <div class="bg-gray-50 rounded-3xl overflow-hidden shadow-sm border border-gray-100 grid md:grid-cols-2 gap-6 items-center">

            {{-- IMAGE --}}
            <div>
                @if($featured->image)
                    <img src="{{ asset('storage/'.$featured->image) }}"
                         class="w-full h-72 object-cover">
                @else
                    <div class="w-full h-72 bg-primary/10 flex items-center justify-center">
                        <span class="text-primary font-black">Uzima Milele</span>
                    </div>
                @endif
            </div>

            {{-- CONTENT --}}
            <div class="p-8">

                <p class="text-primary font-bold mb-2">
                    Tafakari ya leo
                </p>

                <h2 class="text-2xl md:text-3xl font-black text-navy mb-4">
                    {{ $featured->title }}
                </h2>

                <p class="text-gray-600 mb-6">
                    {{ \Illuminate\Support\Str::limit(strip_tags($featured->content), 150) }}
                </p>

                <a href="{{ route('devotions.show', $featured->slug) }}"
                   class="inline-flex bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primaryDark transition">
                    Soma tafakari
                </a>

            </div>

        </div>

    </div>
</section>
@endif

{{-- DEVOTIONS LIST --}}
<section class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">

        @if($devotions->count())

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($devotions as $devotion)

                    <article class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition">

                        {{-- IMAGE --}}
                        @if($devotion->image)
                            <img src="{{ asset('storage/'.$devotion->image) }}"
                                 alt="{{ $devotion->title }}"
                                 class="w-full h-56 object-cover">
                        @else
                            <div class="w-full h-56 bg-primary/10 flex items-center justify-center">
                                <span class="text-primary font-black text-lg">Uzima Milele</span>
                            </div>
                        @endif

                        <div class="p-6">

                            {{-- DATE BADGE --}}
                            <div class="inline-flex bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold mb-4">
                                {{ $devotion->published_at?->format('d M Y') ?? 'Haijapangiwa' }}
                            </div>

                            {{-- TITLE --}}
                            <h3 class="text-xl font-black text-navy mb-3 leading-snug">
                                {{ $devotion->title }}
                            </h3>

                            {{-- EXCERPT --}}
                            <p class="text-gray-600 text-sm leading-relaxed mb-5">
                                {{ \Illuminate\Support\Str::limit(strip_tags($devotion->content), 120) }}
                            </p>

                            {{-- READ MORE --}}
                            <a href="{{ route('devotions.show', $devotion->slug) }}"
                               class="inline-flex items-center gap-1 text-primary font-bold hover:text-primaryDark">
                                Soma zaidi →
                            </a>

                        </div>

                    </article>

                @endforeach
            </div>

            {{-- PAGINATION --}}
            <div class="mt-14 flex justify-center">
                {{ $devotions->links() }}
            </div>

        @else

            <div class="bg-white rounded-3xl p-10 text-center shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-navy mb-3">
                    Hakuna tafakari bado
                </h3>
                <p class="text-gray-600">
                    Tafadhali ongeza tafakari kupitia admin panel na uchague tarehe yake.
                </p>
            </div>

        @endif

    </div>
</section>

@endsection