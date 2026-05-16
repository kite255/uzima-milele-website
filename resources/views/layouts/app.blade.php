<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Uzima Milele')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

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

<body class="font-lato bg-gray-50 text-gray-900 antialiased">

    @php
        $unreadNotificationsCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;
    @endphp

    @includeIf('partials.navbar', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ])

    <main class="min-h-screen">
        @yield('content')
    </main>

    @includeIf('partials.footer', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ])

    {{-- Alpine Collapse plugin for smooth dropdown/accordion --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@if(request()->getHost() === 'new.uzimamilele.or.tz')
    <!-- Elfsight AI Chatbot | Mtumishi Bot -->
    <script src="https://elfsightcdn.com/platform.js" async></script>
    <div class="elfsight-app-3a148ff9-1e5a-4372-9098-4aed0e13b872" data-elfsight-app-lazy></div>
@endif

</body>
</html>