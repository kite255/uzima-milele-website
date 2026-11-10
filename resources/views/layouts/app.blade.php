<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Uzima Milele')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;600;700;900&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }

        /* The chatbot launcher must remain clear of Median's bottom navigation.
           Elfsight can render in an iframe, so also set its vertical offset
           in the Elfsight dashboard if these selectors do not reach it. */
        body.median-app-mode [class*="FloatingButton__FloatingButtonContainer"],
        body.median-app-mode [class*="Widget__Component"],
        body.median-app-mode [class*="Window__Content"] {
            bottom: calc(92px + env(safe-area-inset-bottom, 0px)) !important;
        }

        @media (max-width: 640px) {
            body.median-app-mode [class*="Window__Content"] {
                max-height: calc(100dvh - 116px - env(safe-area-inset-bottom, 0px));
            }
        }

        /* Elfsight may inject a separate floating iframe outside its mount node.
           Shift only the small launcher container, never the full chat window. */
        body.median-app-mode .median-elfsight-launcher-offset {
            transform: translateY(calc(-92px - env(safe-area-inset-bottom, 0px))) !important;
        }

        /* Ensure the Account tab is always reachable if third-party positioning
           changes again; visual overlap is still checked on real devices. */
        body.median-app-mode .median-app-bottom-nav {
            z-index: 2147483646 !important;
            isolation: isolate;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        lato: ['Lato', 'sans-serif'],
                    },

                    colors: {
                        primary: '#0083CB',
                        primaryDark: '#076994',
                        navy: '#0E3D4F',
                        accent: '#F4B122',
                    }
                }
            }
        }
    </script>
</head>

<body class="font-lato bg-gray-50 text-gray-900 antialiased {{ session('median_app_mode') ? 'median-app-mode' : '' }}">

    @php
        $unreadNotificationsCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;
    @endphp

    @if(session('median_app_mode'))
        @include('partials.median-app-navigation')
    @else
        @includeIf('partials.navbar', [
            'unreadNotificationsCount' => $unreadNotificationsCount
        ])
    @endif

    {{-- PAGE CONTENT --}}
    <main class="min-h-screen {{ session('median_app_mode') ? 'pb-28' : '' }}">
        @yield('content')
    </main>

    @unless(session('median_app_mode'))
        @includeIf('partials.footer', [
            'unreadNotificationsCount' => $unreadNotificationsCount
        ])
    @endunless

    {{-- Alpine Collapse plugin for smooth dropdown / accordion --}}
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"
    ></script>

    {{-- Alpine.js --}}
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>

    {{-- Elfsight AI Chatbot | Mtumishi Bot --}}
    <script
        src="https://elfsightcdn.com/platform.js"
        async
    ></script>

    <div
        class="elfsight-app-3a148ff9-1e5a-4372-9098-4aed0e13b872"
        data-elfsight-app-lazy
    ></div>


@if(session('median_app_mode'))
    <script>
        (function () {
            'use strict';

            // Elfsight can render its floating launcher in a small fixed iframe.
            // Offset only that launcher; do not shift full-screen chat dialogs.
            function moveMedianChatLauncher() {
                const frames = document.querySelectorAll('iframe');
                for (const frame of frames) {
                    const src = frame.getAttribute('src') || '';
                    const title = frame.getAttribute('title') || '';
                    if (!/elfsight/i.test(src + ' ' + title)) continue;

                    let target = frame;
                    while (target.parentElement &&
                           target.parentElement !== document.body &&
                           getComputedStyle(target).position !== 'fixed') {
                        target = target.parentElement;
                    }

                    const rect = target.getBoundingClientRect();
                    const fixed = getComputedStyle(target).position === 'fixed';
                    const small = rect.width > 20 && rect.height > 20 &&
                                  rect.width <= 160 && rect.height <= 160;
                    const nearBottom = rect.bottom > window.innerHeight - 150;

                    if (fixed && small && nearBottom) {
                        target.classList.add('median-elfsight-launcher-offset');
                    }
                }
            }

            const observer = new MutationObserver(moveMedianChatLauncher);
            observer.observe(document.body, { childList: true, subtree: true });
            moveMedianChatLauncher();
            window.addEventListener('load', moveMedianChatLauncher, { once: true });
        })();
    </script>
@endif
</body>
</html>