<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Uzima Milele')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Laravel Vite: Tailwind + local Lato/Yang Bagus fonts --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

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
    </style>

    @stack('styles')
</head>

<body class="font-sans bg-gray-50 text-gray-900 antialiased">

    @php
        $unreadNotificationsCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;
    @endphp

    {{-- =====================================================
         MAIN NAVIGATION
         ===================================================== --}}
    @includeIf('partials.navbar', [
        'unreadNotificationsCount' => $unreadNotificationsCount,
    ])


    {{-- =====================================================
         PAGE CONTENT
         ===================================================== --}}
    <main class="min-h-screen">
        @yield('content')
    </main>


    {{-- =====================================================
         FOOTER
         ===================================================== --}}
    @includeIf('partials.footer', [
        'unreadNotificationsCount' => $unreadNotificationsCount,
    ])


    {{-- =====================================================
         PAGE-SPECIFIC SCRIPTS
         ===================================================== --}}
    @stack('scripts')


    {{-- =====================================================
         Alpine Collapse Plugin
         Must load before Alpine.js
         ===================================================== --}}
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"
    ></script>


    {{-- =====================================================
         Alpine.js
         ===================================================== --}}
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>


    {{-- =====================================================
         Elfsight AI Chatbot | Mtumishi Bot
         ===================================================== --}}
    <script
        src="https://elfsightcdn.com/platform.js"
        async
    ></script>

    <div
        class="elfsight-app-3a148ff9-1e5a-4372-9098-4aed0e13b872"
        data-elfsight-app-lazy
    ></div>

</body>
</html>