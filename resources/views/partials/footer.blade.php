<footer
    id="subscription-section"
    class="bg-navy text-white mt-20"
>

    {{-- SUBSCRIBE SECTION --}}
    <div class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-14">

            {{-- SUCCESS MESSAGE --}}
            @if (session('subscription_success'))
                <div
                    class="mb-8 rounded-2xl border border-green-300/30 bg-green-500/10 px-5 py-4"
                    role="status"
                >
                    <div class="flex items-start gap-3">

                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-500/20"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-green-100"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-black text-white">
                                Umejiandikisha kwa mafanikio
                            </p>

                            <p class="mt-1 text-sm leading-relaxed text-green-100">
                                {{ session('subscription_success') }}
                            </p>
                        </div>

                    </div>
                </div>
            @endif


            {{-- VALIDATION ERRORS --}}
            @if ($errors->has('name') || $errors->has('email'))

                <div
                    class="mb-8 rounded-2xl border border-red-300/30 bg-red-500/10 px-5 py-4"
                    role="alert"
                >
                    <p class="mb-2 text-sm font-bold text-red-100">
                        Tafadhali sahihisha taarifa zifuatazo:
                    </p>

                    <ul class="list-disc list-inside space-y-1 text-sm text-red-100/90">
                        @error('name')
                            <li>{{ $message }}</li>
                        @enderror

                        @error('email')
                            <li>{{ $message }}</li>
                        @enderror
                    </ul>
                </div>

            @endif


            <div
                class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
            >

                {{-- TEXT --}}
                <div class="lg:max-w-md">

                    <h2 class="text-2xl sm:text-3xl font-black text-white">
                        Pokea Tafakari Mpya
                    </h2>

                    <p class="mt-2 text-sm sm:text-base text-white/75">
                        Jiandikishe kupokea tafakari na taarifa kutoka Uzima Milele.
                    </p>

                </div>


                {{-- FORM --}}
                <form
                    action="{{ route('email-subscribers.store') }}"
                    method="POST"
                    class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto lg:flex-1 lg:justify-end"
                >
                    @csrf

                    {{-- FULL NAME --}}
                    <div class="w-full lg:max-w-xs">

                        <label
                            for="footer_subscription_name"
                            class="sr-only"
                        >
                            Jina Kamili
                        </label>

                        <input
                            id="footer_subscription_name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Jina lako"
                            autocomplete="name"
                            required
                            class="w-full rounded-full border border-white/20 bg-white px-6 py-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                        >

                    </div>


                    {{-- EMAIL --}}
                    <div class="w-full lg:max-w-sm">

                        <label
                            for="footer_subscription_email"
                            class="sr-only"
                        >
                            Barua Pepe
                        </label>

                        <input
                            id="footer_subscription_email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Barua pepe yako"
                            autocomplete="email"
                            required
                            class="w-full rounded-full border border-white/20 bg-white px-6 py-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-accent focus:ring-accent"
                        >

                    </div>


                    {{-- BUTTON --}}
                    <div>

                        <button
                            type="submit"
                            class="w-full whitespace-nowrap rounded-full bg-primary px-8 py-4 text-sm font-black text-white transition hover:bg-primaryDark sm:w-auto"
                        >
                            Jiandikishe
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>


    {{-- MAIN FOOTER --}}
    <div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">

        {{-- ABOUT --}}
        <div>

            <h3 class="font-bold text-lg mb-4">
                Uzima Milele
            </h3>

            <p class="text-sm text-white/75 leading-relaxed">
                Huduma ya Kikristo inayotoa elimu ya Biblia, afya na jamii kwa lugha ya Kiswahili.
            </p>

        </div>


        {{-- LINKS --}}
        <div>

            <h3 class="font-bold text-lg mb-4">
                Kurasa
            </h3>

            <ul class="space-y-2 text-sm text-white/75">

                <li>
                    <a
                        href="{{ route('home') }}"
                        class="hover:text-white"
                    >
                        Nyumbani
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('about') }}"
                        class="hover:text-white"
                    >
                        Kuhusu sisi
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('devotions.index') }}"
                        class="hover:text-white"
                    >
                        Tafakari
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('lessons.index') }}"
                        class="hover:text-white"
                    >
                        Masomo
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('children.index') }}"
                        class="hover:text-white"
                    >
                        Watoto
                    </a>
                </li>

            </ul>

        </div>


        {{-- SERVICES --}}
        <div>

            <h3 class="font-bold text-lg mb-4">
                Huduma
            </h3>

            <ul class="space-y-2 text-sm text-white/75">

                <li>
                    <a
                        href="{{ route('prayers.testimonies') }}"
                        class="hover:text-white"
                    >
                        Maombi & Ushuhuda
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('contact') }}"
                        class="hover:text-white"
                    >
                        Mawasiliano
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('changia') }}"
                        class="hover:text-white"
                    >
                        Changia
                    </a>
                </li>

            </ul>

        </div>


        {{-- CONTACT --}}
        <div>

            <h3 class="font-bold text-lg mb-4">
                Mawasiliano
            </h3>

            <div class="space-y-2 text-sm text-white/75">

                <p>
                    Dar es Salaam, Tanzania
                </p>

                <p>
                    <a
                        href="mailto:info@uzimamilele.or.tz"
                        class="hover:text-white"
                    >
                        info@uzimamilele.or.tz
                    </a>
                </p>

                <p>
                    <a
                        href="mailto:maombi@uzimamilele.or.tz"
                        class="hover:text-white"
                    >
                        maombi@uzimamilele.or.tz
                    </a>
                </p>

                <p>
                    +255 764 504 284
                </p>

            </div>

        </div>

    </div>


    {{-- COPYRIGHT --}}
    <div
        class="border-t border-white/10 py-4 text-center text-sm text-white/50"
    >
        © {{ date('Y') }} Uzima Milele. All rights reserved.

        <span class="mx-2">
            •
        </span>

        Version 3.2.0
    </div>

</footer>


{{-- RETURN USER TO SUBSCRIPTION MESSAGE AFTER SUBMIT --}}
@if (
    session('subscription_success') ||
    $errors->has('name') ||
    $errors->has('email')
)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subscriptionSection =
                document.getElementById('subscription-section');

            if (subscriptionSection) {
                subscriptionSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    </script>
@endif