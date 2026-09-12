@extends('layouts.app')

@section('content')

@php
    $hasStructuredContent =
        filled($devotion->feature_text) ||
        filled($devotion->lesson) ||
        filled($devotion->scripture_reference) ||
        filled($devotion->scripture_text) ||
        filled($devotion->ellen_white_quote) ||
        filled($devotion->ellen_white_reference);
@endphp


<section class="bg-white">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}
    <section class="bg-white">

        <div class="max-w-6xl mx-auto px-5 md:px-8">

            <div
                class="
                    max-w-4xl
                    mx-auto
                    pt-8
                    md:pt-10
                    pb-8
                    md:pb-10
                "
            >

                <a
                    href="{{ route('devotions.index') }}"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        text-sm
                        font-bold
                        text-primary
                        hover:text-primaryDark
                        transition
                        mb-7
                    "
                >
                    <span aria-hidden="true">←</span>
                    Tafakari zote
                </a>


                <div
                    class="
                        flex
                        items-center
                        gap-3
                        mb-4
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
                            md:text-sm
                            uppercase
                            tracking-[0.16em]
                            font-black
                            text-primaryDark
                        "
                    >
                        Tafakari ya Leo
                    </span>
                </div>


                <h1
                    class="
                        text-4xl
                        md:text-5xl
                        lg:text-[52px]
                        leading-[1.08]
                        font-black
                        tracking-tight
                        text-navy
                        mb-6
                    "
                >
                    {{ $devotion->title }}
                </h1>


                <div
                    class="
                        flex
                        items-center
                        gap-3
                        text-sm
                        text-gray-500
                        font-semibold
                    "
                >
                    <span
                        class="
                            inline-flex
                            items-center
                            justify-center
                            w-8
                            h-8
                            rounded-full
                            bg-accent/15
                            text-accent
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-4 h-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 5.25h15a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V6a.75.75 0 01.75-.75z"
                            />
                        </svg>
                    </span>

                    <span>
                        {{ $devotion->published_at
                            ? \Carbon\Carbon::parse($devotion->published_at)->format('d M Y')
                            : 'Haijapangiwa'
                        }}
                    </span>
                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- SCRIPTURE --}}
    {{-- ========================================================= --}}
    @if(
        filled($devotion->scripture_reference) ||
        filled($devotion->scripture_text)
    )

        <section class="bg-navy">

            <div class="max-w-6xl mx-auto px-5 md:px-8">

                <div
                    class="
                        max-w-4xl
                        mx-auto
                        py-10
                        md:py-12
                    "
                >

                    <div
                        class="
                            text-accent
                            font-serif
                            text-5xl
                            md:text-6xl
                            leading-none
                            h-8
                            mb-4
                        "
                    >
                        “
                    </div>


                    @if(filled($devotion->scripture_text))

                        <blockquote
                            class="
                                m-0
                                max-w-4xl
                                text-white
                                font-serif
                                italic
                                text-2xl
                                md:text-3xl
                                lg:text-[34px]
                                leading-[1.5]
                            "
                        >
                            {{ $devotion->scripture_text }}
                        </blockquote>

                    @endif


                    @if(filled($devotion->scripture_reference))

                        <div
                            class="
                                mt-6
                                flex
                                items-center
                                gap-3
                            "
                        >
                            <span
                                class="
                                    w-8
                                    h-[2px]
                                    bg-accent
                                "
                            ></span>

                            <span
                                class="
                                    text-accent
                                    text-sm
                                    md:text-base
                                    font-black
                                "
                            >
                                {{ $devotion->scripture_reference }}
                            </span>
                        </div>

                    @endif

                </div>

            </div>

        </section>

    @endif


    {{-- ========================================================= --}}
    {{-- MAIN DEVOTION --}}
    {{-- ========================================================= --}}
    <section class="bg-white">

        <div class="max-w-6xl mx-auto px-5 md:px-8">

            <div
                class="
                    max-w-5xl
                    mx-auto
                    py-12
                    md:py-14
                "
            >

                @if($hasStructuredContent)

                    {{-- ================================================= --}}
                    {{-- UJUMBE WA LEO + FEATURED IMAGE --}}
                    {{-- ================================================= --}}
                    @if(filled($devotion->feature_text))

                        <section>

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-3
                                    mb-6
                                "
                            >
                                <span
                                    class="
                                        block
                                        w-10
                                        h-[3px]
                                        bg-primary
                                    "
                                ></span>

                                <h2
                                    class="
                                        m-0
                                        text-sm
                                        md:text-base
                                        uppercase
                                        tracking-[0.14em]
                                        font-black
                                        text-primaryDark
                                    "
                                >
                                    Ujumbe wa Leo
                                </h2>
                            </div>


                            <div
                                class="
                                    grid
                                    grid-cols-1
                                    lg:grid-cols-2
                                    gap-8
                                    lg:gap-10
                                    items-start
                                "
                            >

                                {{-- Message --}}
                                <div
                                    class="
                                        prose
                                        prose-lg
                                        md:prose-xl
                                        max-w-none

                                        text-gray-700

                                        prose-p:text-gray-700
                                        prose-p:text-[18px]
                                        md:prose-p:text-[19px]
                                        prose-p:leading-[1.9]
                                        prose-p:mb-5

                                        prose-strong:text-navy
                                        prose-strong:font-black

                                        prose-a:text-primary
                                        prose-a:font-bold
                                        prose-a:no-underline

                                        hover:prose-a:text-primaryDark
                                        hover:prose-a:underline

                                        prose-ul:my-5
                                        prose-ol:my-5

                                        prose-li:text-gray-700
                                        prose-li:text-[18px]
                                        prose-li:leading-[1.8]
                                        prose-li:my-1
                                    "
                                >
                                    {!! $devotion->feature_text !!}
                                </div>


                                {{-- Featured Image --}}
                                @if($devotion->image)

                                    <div class="lg:-mt-1">

                                        <figure
                                            class="
                                                m-0
                                                overflow-hidden
                                                rounded-3xl
                                                bg-gray-100
                                                border
                                                border-gray-100
                                                shadow-sm
                                            "
                                        >

                                            <img
                                                src="{{ asset('storage/' . $devotion->image) }}"
                                                alt="{{ $devotion->title }}"
                                                class="
                                                    block
                                                    w-full
                                                    h-auto
                                                "
                                            >

                                        </figure>

                                    </div>

                                @endif

                            </div>

                        </section>

                    @endif


                    {{-- ================================================= --}}
                    {{-- FUNZO LA LEO --}}
                    {{-- ================================================= --}}
                    @if(filled($devotion->lesson))

                        <section
                            class="
                                mt-12
                                md:mt-14
                            "
                        >

                            <div
                                class="
                                    relative
                                    overflow-hidden
                                    rounded-3xl
                                    bg-[#F4F9F2]
                                    border
                                    border-green/20
                                    px-6
                                    py-7
                                    md:px-9
                                    md:py-9
                                "
                            >

                                <div
                                    class="
                                        absolute
                                        -top-20
                                        -right-20
                                        w-44
                                        h-44
                                        rounded-full
                                        bg-green/10
                                        pointer-events-none
                                    "
                                ></div>


                                <div class="relative">

                                    <div
                                        class="
                                            flex
                                            items-center
                                            gap-4
                                            mb-5
                                        "
                                    >

                                        <span
                                            class="
                                                inline-flex
                                                shrink-0
                                                items-center
                                                justify-center
                                                w-11
                                                h-11
                                                rounded-full
                                                bg-green
                                                text-white
                                                font-black
                                                text-lg
                                            "
                                        >
                                            ✓
                                        </span>


                                        <div>

                                            <div
                                                class="
                                                    text-xs
                                                    uppercase
                                                    tracking-[0.14em]
                                                    text-green
                                                    font-black
                                                    mb-1
                                                "
                                            >
                                                Chukua hatua
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
                                                Funzo la Leo
                                            </h2>

                                        </div>

                                    </div>


                                    <div
                                        class="
                                            prose
                                            prose-lg
                                            max-w-none

                                            prose-p:text-gray-700
                                            prose-p:text-[18px]
                                            prose-p:leading-[1.85]
                                            prose-p:mb-5

                                            prose-strong:text-navy

                                            prose-a:text-primary
                                            prose-a:font-bold

                                            prose-ul:my-5
                                            prose-ol:my-5

                                            prose-li:text-gray-700
                                            prose-li:text-[18px]
                                            prose-li:leading-[1.8]
                                        "
                                    >
                                        {!! $devotion->lesson !!}
                                    </div>

                                </div>

                            </div>

                        </section>

                    @endif


                    {{-- ================================================= --}}
                    {{-- ELLEN G. WHITE --}}
                    {{-- ================================================= --}}
                    @if(
                        filled($devotion->ellen_white_quote) ||
                        filled($devotion->ellen_white_reference)
                    )

                        <section
                            class="
                                mt-14
                                md:mt-16
                                pt-12
                                md:pt-14
                                border-t
                                border-gray-200
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-center
                                    gap-4
                                    mb-7
                                "
                            >

                                <span
                                    class="
                                        w-12
                                        h-px
                                        bg-gray-300
                                    "
                                ></span>

                                <span
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-[0.18em]
                                        font-black
                                        text-primaryDark
                                        text-center
                                    "
                                >
                                    Ellen G. White
                                </span>

                                <span
                                    class="
                                        w-12
                                        h-px
                                        bg-gray-300
                                    "
                                ></span>

                            </div>


                            @if(filled($devotion->ellen_white_quote))

                                <blockquote
                                    class="
                                        max-w-3xl
                                        mx-auto
                                        m-0
                                        text-center
                                        font-serif
                                        italic
                                        text-2xl
                                        md:text-3xl
                                        leading-[1.6]
                                        text-navy
                                    "
                                >
                                    “{{ $devotion->ellen_white_quote }}”
                                </blockquote>

                            @endif


                            @if(filled($devotion->ellen_white_reference))

                                <div
                                    class="
                                        mt-6
                                        text-center
                                        text-sm
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    {{ $devotion->ellen_white_reference }}
                                </div>

                            @endif

                        </section>

                    @endif


                {{-- ================================================= --}}
                {{-- LEGACY DEVOTIONS --}}
                {{-- ================================================= --}}
                @elseif(filled($devotion->content))

                    <div
                        class="
                            max-w-4xl
                            mx-auto

                            prose
                            prose-lg
                            md:prose-xl
                            max-w-none

                            prose-p:text-gray-700
                            prose-p:text-[18px]
                            md:prose-p:text-[19px]
                            prose-p:leading-[1.9]
                            prose-p:mb-5

                            prose-strong:text-navy

                            prose-a:text-primary
                            prose-a:font-bold

                            prose-ul:my-5
                            prose-ol:my-5

                            prose-li:text-gray-700
                            prose-li:text-[18px]
                            prose-li:leading-[1.8]
                        "
                    >
                        {!! $devotion->content !!}
                    </div>

                @endif

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- BOTTOM ACTIONS --}}
    {{-- ========================================================= --}}
    <section
        class="
            bg-gray-50
            border-t
            border-gray-100
        "
    >

        <div class="max-w-6xl mx-auto px-5 md:px-8">

            <div
                class="
                    max-w-4xl
                    mx-auto
                    py-9
                    md:py-11
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        sm:flex-row
                        gap-4
                        sm:items-center
                        sm:justify-between
                    "
                >

                    <a
                        href="{{ route('devotions.index') }}"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            px-6
                            py-3
                            rounded-xl
                            bg-white
                            border
                            border-gray-200
                            text-navy
                            font-bold
                            hover:border-primary/30
                            hover:text-primary
                            transition
                        "
                    >
                        <span aria-hidden="true">←</span>
                        Tafakari nyingine
                    </a>


                    <a
                        href="/contact"
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
                            transition
                        "
                    >
                        Tuma maombi / ushuhuda
                    </a>

                </div>

            </div>

        </div>

    </section>

</section>

{{-- ========================================================= --}}
{{-- SHARE DEVOTION --}}
{{-- ========================================================= --}}
<section class="bg-white">

    <div class="max-w-6xl mx-auto px-5 md:px-8">

        <div
            class="
                max-w-4xl
                mx-auto
                py-8
                md:py-10
                border-t
                border-gray-200
            "
        >

            <div
                class="
                    flex
                    flex-col
                    md:flex-row
                    md:items-center
                    md:justify-between
                    gap-5
                "
            >

                {{-- Share label --}}
                <div>

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                            text-xs
                            uppercase
                            tracking-[0.16em]
                            font-black
                            text-primaryDark
                            mb-1
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-4 h-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7.217 10.907a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.783 6.407a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.783 20.093a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM9.18 9.824l5.64-3.648M9.18 11.176l5.64 3.648"
                            />
                        </svg>

                        Shiriki Tafakari
                    </div>

                    <p class="m-0 text-sm text-gray-500">
                        Shiriki ujumbe huu na rafiki au familia.
                    </p>

                </div>


                {{-- Share buttons --}}
                <div class="flex flex-wrap gap-3">

                    {{-- Native Share --}}
                    <button
                        type="button"
                        id="share-devotion-button"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            px-4
                            py-2.5
                            rounded-xl
                            bg-primary
                            text-white
                            text-sm
                            font-bold
                            hover:bg-primaryDark
                            transition
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-4 h-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7.217 10.907a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.783 6.407a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.783 20.093a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM9.18 9.824l5.64-3.648M9.18 11.176l5.64 3.648"
                            />
                        </svg>

                        <span>Shiriki</span>
                    </button>


                    {{-- WhatsApp --}}
                    <a
                        href="https://wa.me/?text={{ urlencode($devotion->title . ' - ' . route('devotions.show', $devotion->slug)) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            px-4
                            py-2.5
                            rounded-xl
                            border
                            border-gray-200
                            bg-white
                            text-navy
                            text-sm
                            font-bold
                            hover:bg-gray-50
                            hover:border-green/30
                            transition
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="w-4 h-4 text-green"
                        >
                            <path d="M12.04 2C6.52 2 2.04 6.48 2.04 12c0 1.76.46 3.48 1.34 4.99L2 22l5.16-1.35A9.95 9.95 0 0012.04 22C17.56 22 22 17.52 22 12S17.56 2 12.04 2zm0 18.18a8.2 8.2 0 01-4.18-1.14l-.3-.18-3.06.8.82-2.98-.2-.31A8.14 8.14 0 013.86 12c0-4.51 3.67-8.18 8.18-8.18 4.5 0 8.14 3.67 8.14 8.18 0 4.51-3.64 8.18-8.14 8.18zm4.48-6.12c-.24-.12-1.45-.72-1.67-.8-.23-.08-.39-.12-.56.12-.16.24-.64.8-.79.96-.14.16-.29.18-.53.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.22-1.45-1.36-1.69-.14-.24-.02-.37.1-.49.11-.11.25-.28.37-.42.12-.14.16-.24.25-.4.08-.16.04-.3-.02-.42-.06-.12-.56-1.35-.76-1.85-.2-.48-.41-.42-.56-.43h-.48c-.16 0-.43.06-.65.3-.23.24-.86.84-.86 2.04 0 1.2.88 2.36 1 2.52.12.16 1.73 2.64 4.2 3.7.59.25 1.05.4 1.41.52.59.19 1.13.16 1.55.1.47-.07 1.45-.59 1.66-1.16.2-.57.2-1.06.14-1.16-.06-.1-.23-.16-.47-.28z"/>
                        </svg>

                        WhatsApp
                    </a>


                    {{-- Facebook --}}
<a
    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('devotions.show', $devotion->slug)) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="
        inline-flex
        items-center
        justify-center
        gap-2
        px-4
        py-2.5
        rounded-xl
        border
        border-gray-200
        bg-white
        text-navy
        text-sm
        font-bold
        hover:bg-gray-50
        hover:border-primary/30
        transition
    "
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="w-4 h-4 text-primary"
    >
        <path d="M13.5 22v-9h3l.45-3H13.5V8.08c0-.87.24-1.46 1.52-1.46H17V3.94c-.34-.05-1.5-.14-2.85-.14-2.82 0-4.75 1.72-4.75 4.88V10H6v3h3.4v9h4.1z"/>
    </svg>

    Facebook
</a>

                    {{-- Copy Link --}}
                    <button
                        type="button"
                        id="copy-devotion-link"
                        data-url="{{ route('devotions.show', $devotion->slug) }}"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            px-4
                            py-2.5
                            rounded-xl
                            border
                            border-gray-200
                            bg-white
                            text-navy
                            text-sm
                            font-bold
                            hover:bg-gray-50
                            hover:border-primary/30
                            transition
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-4 h-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 6H19.5a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5h-12A1.5 1.5 0 016 19.5V13.5M4.5 3h9A1.5 1.5 0 0115 4.5v9A1.5 1.5 0 0113.5 15h-9A1.5 1.5 0 013 13.5v-9A1.5 1.5 0 014.5 3z"
                            />
                        </svg>

                        <span>Nakili Link</span>
                    </button>

                </div>

            </div>

        </div>

    </div>

</section>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const shareButton = document.getElementById('share-devotion-button');
        const copyButton = document.getElementById('copy-devotion-link');

        const shareData = {
            title: @json($devotion->title),
            text: @json('Soma tafakari hii kutoka Uzima Milele: ' . $devotion->title),
            url: @json(route('devotions.show', $devotion->slug)),
        };

        if (shareButton) {
            shareButton.addEventListener('click', async function () {
                if (navigator.share) {
                    try {
                        await navigator.share(shareData);
                    } catch (error) {
                        // User closed the share dialog.
                    }
                } else {
                    try {
                        await navigator.clipboard.writeText(shareData.url);

                        const text = shareButton.querySelector('span');
                        const originalText = text.textContent;

                        text.textContent = 'Imenakiliwa';

                        setTimeout(() => {
                            text.textContent = originalText;
                        }, 2000);
                    } catch (error) {
                        window.prompt('Nakili link hii:', shareData.url);
                    }
                }
            });
        }

        if (copyButton) {
            copyButton.addEventListener('click', async function () {
                const url = copyButton.dataset.url;
                const text = copyButton.querySelector('span');

                try {
                    await navigator.clipboard.writeText(url);

                    const originalText = text.textContent;

                    text.textContent = 'Imenakiliwa';

                    setTimeout(() => {
                        text.textContent = originalText;
                    }, 2000);
                } catch (error) {
                    window.prompt('Nakili link hii:', url);
                }
            });
        }
    });
</script>


@endsection