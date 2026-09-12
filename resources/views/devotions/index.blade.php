@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Devotion excerpt helper
    |--------------------------------------------------------------------------
    |
    | New devotions use structured fields, while older devotions may still
    | contain the legacy "content" field.
    |
    | Priority:
    | 1. feature_text
    | 2. lesson
    | 3. content
    | 4. scripture_text
    |
    */

    $devotionExcerpt = function ($devotion, int $limit = 130) {

        $source =
            $devotion->feature_text
            ?: $devotion->lesson
            ?: $devotion->content
            ?: $devotion->scripture_text;

        if (blank($source)) {
            return null;
        }

        $text = strip_tags($source);

        $text = html_entity_decode(
            $text,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace('/\s+/u', ' ', $text);

        $text = trim($text);

        return \Illuminate\Support\Str::limit(
            $text,
            $limit
        );
    };
@endphp


{{-- ========================================================= --}}
{{-- HERO / BANNER --}}
{{-- ========================================================= --}}
<section
    class="
        relative

        h-[230px]
        md:h-[300px]

        flex
        items-center
        justify-center

        overflow-hidden

        bg-navy
    "
>

    <img
        src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600&auto=format&fit=crop"
        alt="Biblia kwa ajili ya tafakari"
        class="
            absolute
            inset-0

            w-full
            h-full

            object-cover
        "
    >

    <div
        class="
            absolute
            inset-0

            bg-navy/75
        "
    ></div>


    <div
        class="
            relative
            z-10

            text-center

            px-5
        "
    >

        <h1
            class="
                m-0

                text-4xl
                md:text-5xl

                font-black

                text-white
            "
        >
            Tafakari
        </h1>


        <p
            class="
                mt-3

                max-w-2xl
                mx-auto

                text-sm
                md:text-base

                leading-relaxed

                text-white/90
            "
        >
            Soma tafakari kulingana na tarehe na uendelee kukua kiroho kila siku.
        </p>

    </div>

</section>



{{-- ========================================================= --}}
{{-- FEATURED DEVOTION --}}
{{-- ========================================================= --}}
@if($featured)

    @php
        $featuredExcerpt = $devotionExcerpt($featured, 155);
    @endphp

    <section
        class="
            bg-white

            py-10
            md:py-12
        "
    >

        <div
            class="
                max-w-6xl
                mx-auto

                px-5
                md:px-8
            "
        >

            <article
                class="
                    overflow-hidden

                    rounded-3xl

                    bg-gray-50

                    border
                    border-gray-100

                    shadow-sm

                    grid
                    grid-cols-1
                    md:grid-cols-2

                    transition
                    duration-300

                    hover:shadow-md
                "
            >

                {{-- ===================================================== --}}
                {{-- FEATURED IMAGE --}}
                {{-- ===================================================== --}}
                <div
                    class="
                        relative

                        min-h-[260px]
                        md:min-h-[340px]

                        bg-primary/10
                    "
                >

                    @if($featured->image)

                        <img
                            src="{{ asset('storage/' . $featured->image) }}"
                            alt="{{ $featured->title }}"
                            class="
                                absolute
                                inset-0

                                block

                                w-full
                                h-full

                                object-cover
                            "
                        >

                    @else

                        <div
                            class="
                                absolute
                                inset-0

                                flex
                                items-center
                                justify-center

                                bg-primary/10
                            "
                        >

                            <span
                                class="
                                    text-primary

                                    text-lg

                                    font-black
                                "
                            >
                                Uzima Milele
                            </span>

                        </div>

                    @endif

                </div>


                {{-- ===================================================== --}}
                {{-- FEATURED CONTENT --}}
                {{-- ===================================================== --}}
                <div
                    class="
                        flex
                        flex-col
                        justify-center

                        p-7
                        md:p-10
                        lg:p-12
                    "
                >

                    <div
                        class="
                            mb-3

                            text-sm

                            font-black

                            text-primary
                        "
                    >
                        Tafakari ya leo
                    </div>


                    <h2
                        class="
                            m-0

                            text-2xl
                            md:text-3xl

                            leading-tight

                            font-black

                            text-navy
                        "
                    >
                        {{ $featured->title }}
                    </h2>


                    @if(filled($featuredExcerpt))

                        <p
                            class="
                                mt-4
                                mb-0

                                max-w-xl

                                text-[15px]
                                md:text-base

                                leading-7

                                text-gray-600
                            "
                        >
                            {{ $featuredExcerpt }}
                        </p>

                    @endif


                    <div
                        class="
                            mt-7
                        "
                    >

                        <a
                            href="{{ route('devotions.show', $featured->slug) }}"
                            class="
                                inline-flex
                                items-center
                                justify-center

                                px-6
                                py-3

                                rounded-xl

                                bg-primary

                                text-white

                                font-bold

                                hover:bg-primaryDark

                                transition-colors
                                duration-200
                            "
                        >
                            Soma Tafakari
                        </a>

                    </div>

                </div>

            </article>

        </div>

    </section>

@endif



{{-- ========================================================= --}}
{{-- DEVOTIONS LIST --}}
{{-- ========================================================= --}}
<section
    class="
        bg-gray-50

        py-10
        md:py-14
    "
>

    <div
        class="
            max-w-7xl
            mx-auto

            px-5
            md:px-8
        "
    >

        @if($devotions->count())


            {{-- ========================================================= --}}
            {{-- SECTION HEADING --}}
            {{-- ========================================================= --}}
            <div
                class="
                    mb-8
                    md:mb-10
                "
            >

                <div
                    class="
                        flex
                        items-center
                        gap-3

                        mb-3
                    "
                >

                    <span
                        class="
                            block

                            w-9
                            h-[2px]

                            bg-accent
                        "
                    ></span>


                    <span
                        class="
                            text-xs

                            uppercase
                            tracking-[0.14em]

                            font-black

                            text-primaryDark
                        "
                    >
                        Tafakari zaidi
                    </span>

                </div>


                <h2
                    class="
                        m-0

                        text-2xl
                        md:text-3xl

                        font-black

                        text-navy
                    "
                >
                    Endelea kusoma
                </h2>

            </div>



            {{-- ========================================================= --}}
            {{-- CARDS --}}
            {{-- ========================================================= --}}
            <div
                class="
                    grid

                    grid-cols-1
                    sm:grid-cols-2
                    lg:grid-cols-3

                    gap-6
                    lg:gap-8
                "
            >

                @foreach($devotions as $devotion)

                    @php
                        $excerpt = $devotionExcerpt($devotion, 120);
                    @endphp


                    <article
                        class="
                            group

                            flex
                            flex-col

                            h-full

                            overflow-hidden

                            rounded-3xl

                            bg-white

                            border
                            border-gray-100

                            shadow-sm

                            transition-all
                            duration-300

                            hover:-translate-y-1
                            hover:shadow-lg
                        "
                    >

                        {{-- ================================================= --}}
                        {{-- IMAGE --}}
                        {{-- ================================================= --}}
                        <a
                            href="{{ route('devotions.show', $devotion->slug) }}"
                            class="
                                block

                                overflow-hidden

                                bg-primary/10
                            "
                        >

                            @if($devotion->image)

                                <img
                                    src="{{ asset('storage/' . $devotion->image) }}"
                                    alt="{{ $devotion->title }}"
                                    loading="lazy"
                                    class="
                                        block

                                        w-full
                                        h-52

                                        object-cover

                                        transition-transform
                                        duration-500

                                        group-hover:scale-[1.03]
                                    "
                                >

                            @else

                                <div
                                    class="
                                        w-full
                                        h-52

                                        flex
                                        items-center
                                        justify-center

                                        bg-primary/10
                                    "
                                >

                                    <span
                                        class="
                                            text-primary

                                            text-lg

                                            font-black
                                        "
                                    >
                                        Uzima Milele
                                    </span>

                                </div>

                            @endif

                        </a>



                        {{-- ================================================= --}}
                        {{-- CARD CONTENT --}}
                        {{-- ================================================= --}}
                        <div
                            class="
                                flex
                                flex-col
                                flex-1

                                p-6
                            "
                        >

                            {{-- DATE --}}
                            <div
                                class="
                                    inline-flex
                                    self-start

                                    items-center

                                    px-3
                                    py-1

                                    rounded-full

                                    bg-primary/10

                                    text-xs

                                    font-bold

                                    text-primary

                                    mb-4
                                "
                            >
                                {{ $devotion->published_at?->format('d M Y') ?? 'Haijapangiwa' }}
                            </div>


                            {{-- TITLE --}}
                            <h3
                                class="
                                    m-0

                                    text-xl

                                    leading-snug

                                    font-black

                                    text-navy

                                    transition-colors

                                    group-hover:text-primary
                                "
                            >

                                <a
                                    href="{{ route('devotions.show', $devotion->slug) }}"
                                    class="hover:no-underline"
                                >
                                    {{ $devotion->title }}
                                </a>

                            </h3>


                            {{-- EXCERPT --}}
                            <div
                                class="
                                    mt-3

                                    min-h-[72px]
                                "
                            >

                                @if(filled($excerpt))

                                    <p
                                        class="
                                            m-0

                                            text-sm

                                            leading-6

                                            text-gray-600
                                        "
                                    >
                                        {{ $excerpt }}
                                    </p>

                                @endif

                            </div>


                            {{-- READ MORE --}}
                            <div
                                class="
                                    mt-auto
                                    pt-5
                                "
                            >

                                <a
                                    href="{{ route('devotions.show', $devotion->slug) }}"
                                    class="
                                        inline-flex
                                        items-center
                                        gap-2

                                        text-sm

                                        font-bold

                                        text-primary

                                        hover:text-primaryDark

                                        transition-colors
                                    "
                                >

                                    <span>
                                        Soma zaidi
                                    </span>

                                    <span
                                        aria-hidden="true"
                                        class="
                                            transition-transform
                                            duration-200

                                            group-hover:translate-x-1
                                        "
                                    >
                                        →
                                    </span>

                                </a>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>



            {{-- ========================================================= --}}
            {{-- PAGINATION --}}
            {{-- ========================================================= --}}
            <div
                class="
                    mt-10
                    md:mt-12

                    flex
                    justify-center
                "
            >
                {{ $devotions->links() }}
            </div>


        @else


            {{-- ========================================================= --}}
            {{-- EMPTY STATE --}}
            {{-- ========================================================= --}}
            <div
                class="
                    max-w-2xl
                    mx-auto

                    rounded-3xl

                    bg-white

                    p-8
                    md:p-10

                    text-center

                    shadow-sm

                    border
                    border-gray-100
                "
            >

                <h3
                    class="
                        m-0

                        text-2xl

                        font-black

                        text-navy
                    "
                >
                    Hakuna tafakari bado
                </h3>


                <p
                    class="
                        mt-3
                        mb-0

                        text-gray-600

                        leading-relaxed
                    "
                >
                    Tafadhali ongeza tafakari kupitia admin panel na uchague tarehe yake.
                </p>

            </div>

        @endif

    </div>

</section>

@endsection