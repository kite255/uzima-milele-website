@extends('layouts.app')

@section('title', 'Notifications')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="relative overflow-hidden mb-8 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Notifications
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Taarifa Zangu
                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Tazama taarifa za masomo, maswali, majibu na vyeti.
                </p>
            </div>

            <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="absolute right-32 top-8 w-24 h-24 rounded-full bg-white/10"></div>
        </div>

        {{-- ACTIONS --}}
        <div class="mb-6 flex flex-col sm:flex-row justify-between gap-3">
            <a href="{{ route('dashboard') }}"
               class="inline-flex justify-center rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi Dashboard
            </a>

            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf

                    <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center rounded-xl bg-primary px-5 py-3 text-white font-bold hover:bg-primaryDark transition">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-6 py-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        {{-- LIST --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-2xl font-black text-navy">
                    Orodha ya Taarifa
                </h2>

                <span class="text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
                    {{ auth()->user()->unreadNotifications()->count() }} mpya
                </span>
            </div>

            <div class="divide-y">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <a href="{{ route('notifications.read', $notification->id) }}"
                       class="block p-6 transition {{ $isUnread ? 'bg-primary/5 hover:bg-primary/10' : 'hover:bg-gray-50' }}">
                        <div class="flex gap-4">
                            <div class="shrink-0">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $isUnread ? 'bg-primary text-white' : 'bg-gray-100 text-navy' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <h3 class="font-black text-navy">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h3>

                                    <div class="flex items-center gap-2">
                                        @if($isUnread)
                                            <span class="px-3 py-1 rounded-full bg-accent/20 text-navy text-xs font-bold">
                                                New
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-400">
                                            {{ $notification->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mt-2 text-gray-600 leading-relaxed">
                                    {{ $data['message'] ?? 'Una taarifa mpya.' }}
                                </p>

                                <p class="mt-3 text-sm font-bold text-primary">
                                    Fungua →
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center">
                        <h3 class="text-xl font-black text-navy">
                            Hakuna notifications bado.
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Taarifa zako zitaonekana hapa baada ya kujiunga na masomo, kuuliza maswali au kupata cheti.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>

    </div>
</section>

@endsection