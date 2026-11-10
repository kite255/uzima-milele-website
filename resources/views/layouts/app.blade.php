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

        /* Keep Elfsight launcher above the 68px app tab bar + safe area.
           Elfsight may render in a cross-origin iframe: these rules can only
           affect host-page elements, not styles inside that iframe. */
        body.median-app-mode {
            --median-chat-bottom: calc(100px + env(safe-area-inset-bottom, 0px));
        }

        body.median-app-mode [class*="FloatingButton__FloatingButtonContainer"] {
            bottom: var(--median-chat-bottom) !important;
        }

        body.median-app-mode [class*="Window__Content"] {
            bottom: var(--median-chat-bottom) !important;
            max-height: calc(100dvh - 120px - env(safe-area-inset-bottom, 0px));
        }

        /* Do not conceal the launcher behind an artificially high nav z-index. */
        body.median-app-mode .median-app-bottom-nav {
            z-index: 50;
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
        (() => {
            'use strict';
            // A floating Elfsight iframe can sit outside its original mount.
            // Move only small, bottom-right, fixed-position launcher hosts.
            // Never shift the full chat window or unrelated third-party widgets.
            const offset = 'calc(100px + env(safe-area-inset-bottom, 0px))';
            const launcherSelector = '[class*="FloatingButton__FloatingButtonContainer"]';
            const widgetId = '3a148ff9-1e5a-4372-9098-4aed0e13b872';

            function positionChat() {
                document.querySelectorAll(launcherSelector).forEach(node => {
                    node.style.setProperty('bottom', offset, 'important');
                });

                document.querySelectorAll('iframe').forEach(frame => {
                    const identity = [
                        frame.getAttribute('src') || '',
                        frame.getAttribute('title') || '',
                        frame.id || '',
                        frame.className || ''
                    ].join(' ').toLowerCase();
                    if (!identity.includes('elfsight') && !identity.includes(widgetId)) return;

                    let node = frame;
                    while (node.parentElement && node.parentElement !== document.body
                        && getComputedStyle(node).position !== 'fixed') {
                        node = node.parentElement;
                    }
                    if (getComputedStyle(node).position !== 'fixed') return;
                    const rect = node.getBoundingClientRect();
                    // A floating launcher is compact. Don't move expanded chats.
                    if (rect.width < 24 || rect.width > 160 ||
                        rect.height < 24 || rect.height > 160 ||
                        rect.right < window.innerWidth - 200) return;
                    node.style.setProperty('bottom', offset, 'important');
                    node.style.setProperty('top', 'auto', 'important');
                });
            }

            let scheduled = false;
            function schedulePosition() {
                if (scheduled) return;
                scheduled = true;
                requestAnimationFrame(() => {
                    scheduled = false;
                    positionChat();
                });
            }
            const observer = new MutationObserver(schedulePosition);
            observer.observe(document.body, {childList: true, subtree: true});
            schedulePosition();
            window.addEventListener('load', schedulePosition, {once: true});
        })();
    </script>
@endif
</body>
</html>