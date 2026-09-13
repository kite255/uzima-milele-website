<footer
    id="subscription-section"
    class="bg-navy text-white mt-20"
>

    <div class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-14">

            @if (
                $errors->has('name') ||
                $errors->has('email') ||
                $errors->has('phone') ||
                $errors->has('consent')
            )
                <div
                    class="mb-8 rounded-2xl border border-red-300/30 bg-red-500/10 px-5 py-4"
                    role="alert"
                    aria-live="assertive"
                >
                    <p class="mb-2 text-sm font-bold text-red-100">
                        Tafadhali sahihisha taarifa zifuatazo:
                    </p>

                    <ul class="list-inside list-disc space-y-1 text-sm text-red-100/90">
                        @error('name')
                            <li>{{ $message }}</li>
                        @enderror

                        @error('email')
                            <li>{{ $message }}</li>
                        @enderror

                        @error('phone')
                            <li>{{ $message }}</li>
                        @enderror

                        @error('consent')
                            <li>{{ $message }}</li>
                        @enderror
                    </ul>
                </div>
            @endif

            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">

                <div class="lg:max-w-md lg:pt-2">
                    <h2 class="text-2xl font-black text-white sm:text-3xl">
                        Pokea Tafakari Mpya
                    </h2>

                    <p class="mt-2 text-sm text-white/75 sm:text-base">
                        Jiandikishe kupokea tafakari na taarifa kutoka Uzima Milele.
                    </p>

                    <p class="mt-3 text-xs leading-relaxed text-white/55">
                        Unaweza kubadilisha mapendeleo yako au kujiondoa wakati wowote
                        kupitia kiungo kilicho kwenye barua pepe.
                    </p>
                </div>

                <form
                    action="{{ route('email-subscribers.store') }}"
                    method="POST"
                    class="w-full lg:max-w-3xl"
                >
                    @csrf

                    <div class="flex flex-col gap-3 sm:flex-row">

                        <div class="w-full sm:flex-1">
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

                        <div class="w-full sm:flex-1">
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

                        <div class="sm:shrink-0">
                            <button
                                type="submit"
                                class="flex min-h-[52px] w-full items-center justify-center whitespace-nowrap rounded-full bg-primary px-8 py-4 text-sm font-black text-white transition hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-navy sm:w-auto"
                            >
                                Jiandikishe
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label
                            for="footer_subscription_consent"
                            class="flex cursor-pointer items-start gap-3"
                        >
                            <input
                                id="footer_subscription_consent"
                                type="checkbox"
                                name="consent"
                                value="1"
                                @checked(old('consent'))
                                required
                                class="mt-0.5 rounded border-white/30 bg-white text-primary focus:ring-accent"
                            >

                            <span class="text-xs leading-relaxed text-white/70">
                                Ninakubali kupokea tafakari, masomo na taarifa
                                kutoka Uzima Milele kwa barua pepe.
                            </span>
                        </label>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto grid gap-8 px-4 py-12 md:grid-cols-4">

        <div>
            <h3 class="mb-4 text-lg font-bold">
                Uzima Milele
            </h3>

            <p class="text-sm leading-relaxed text-white/75">
                Huduma ya Kikristo inayotoa elimu ya Biblia, afya na jamii
                kwa lugha ya Kiswahili.
            </p>
        </div>

        <div>
            <h3 class="mb-4 text-lg font-bold">
                Kurasa
            </h3>

            <ul class="space-y-2 text-sm text-white/75">
                <li>
                    <a href="{{ route('home') }}" class="transition hover:text-white">
                        Nyumbani
                    </a>
                </li>

                <li>
                    <a href="{{ route('about') }}" class="transition hover:text-white">
                        Kuhusu sisi
                    </a>
                </li>

                <li>
                    <a href="{{ route('devotions.index') }}" class="transition hover:text-white">
                        Tafakari
                    </a>
                </li>

                <li>
                    <a href="{{ route('lessons.index') }}" class="transition hover:text-white">
                        Masomo
                    </a>
                </li>

                <li>
                    <a href="{{ route('children.index') }}" class="transition hover:text-white">
                        Watoto
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <h3 class="mb-4 text-lg font-bold">
                Huduma
            </h3>

            <ul class="space-y-2 text-sm text-white/75">
                <li>
                    <a href="{{ route('prayers.testimonies') }}" class="transition hover:text-white">
                        Maombi & Ushuhuda
                    </a>
                </li>

                <li>
                    <a href="{{ route('contact') }}" class="transition hover:text-white">
                        Mawasiliano
                    </a>
                </li>

                <li>
                    <a href="{{ route('changia') }}" class="transition hover:text-white">
                        Changia
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <h3 class="mb-4 text-lg font-bold">
                Mawasiliano
            </h3>

            <div class="space-y-2 text-sm text-white/75">
                <p>Dar es Salaam, Tanzania</p>

                <p>
                    <a
                        href="mailto:info@uzimamilele.or.tz"
                        class="transition hover:text-white"
                    >
                        info@uzimamilele.or.tz
                    </a>
                </p>

                <p>
                    <a
                        href="mailto:maombi@uzimamilele.or.tz"
                        class="transition hover:text-white"
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

    <div class="border-t border-white/10 py-4 text-center text-sm text-white/50">
        © {{ date('Y') }} Uzima Milele. All rights reserved.

        <span class="mx-2">•</span>

        Version 3.3.0
    </div>

</footer>

@if (session('subscription_success'))
    <div
        id="subscription-success-toast"
        class="fixed right-4 top-[115px] z-[9999] w-[calc(100%-2rem)] max-w-lg overflow-hidden rounded-3xl border-2 border-green-300 bg-white shadow-2xl transition-all duration-300 sm:right-8 sm:top-[125px]"
        role="status"
        aria-live="polite"
        aria-atomic="true"
    >
        <div class="h-1.5 bg-green-500"></div>

        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">

                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-green-100">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7 text-green-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-lg font-black leading-tight text-navy">
                        {{
                            session(
                                'subscription_success_title',
                                'Usajili Umefanikiwa'
                            )
                        }}
                    </p>

                    <p class="mt-2 text-sm leading-6 text-gray-700">
                        {{ session('subscription_success') }}
                    </p>

                    @if (session('subscription_success_hint'))
                        <p class="mt-2 text-xs leading-relaxed text-gray-500">
                            {{ session('subscription_success_hint') }}
                        </p>
                    @endif
                </div>

                <button
                    type="button"
                    id="subscription-toast-close"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary"
                    aria-label="Funga ujumbe"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

            </div>

            <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-green-100">
                <div
                    id="subscription-toast-progress"
                    class="h-full w-full rounded-full bg-green-500"
                ></div>
            </div>
        </div>
    </div>
@endif

@if (
    $errors->has('name') ||
    $errors->has('email') ||
    $errors->has('phone') ||
    $errors->has('consent')
)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subscriptionSection =
                document.getElementById('subscription-section');

            if (!subscriptionSection) {
                return;
            }

            subscriptionSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    </script>
@endif

@if (session('subscription_success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast =
                document.getElementById('subscription-success-toast');

            const closeButton =
                document.getElementById('subscription-toast-close');

            const progress =
                document.getElementById('subscription-toast-progress');

            if (!toast) {
                return;
            }

            const duration = 6000;
            let hideTimer = null;
            let removed = false;

            const hideToast = function () {
                if (removed) {
                    return;
                }

                removed = true;

                toast.classList.add(
                    'opacity-0',
                    '-translate-y-4',
                    'pointer-events-none'
                );

                setTimeout(function () {
                    if (toast && toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            };

            if (closeButton) {
                closeButton.addEventListener('click', function () {
                    if (hideTimer) {
                        clearTimeout(hideTimer);
                    }

                    hideToast();
                });
            }

            if (progress) {
                progress.style.transition =
                    'width ' + duration + 'ms linear';

                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        progress.style.width = '0%';
                    });
                });
            }

            hideTimer = setTimeout(
                hideToast,
                duration
            );
        });
    </script>
@endif
