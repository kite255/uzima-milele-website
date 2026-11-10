@extends('layouts.app')

@section('title', 'Akaunti - Uzima Milele')

@section('content')
<section class="mx-auto w-full max-w-2xl px-4 pb-8 pt-6 sm:pt-10">
    <div class="mb-5">
        <h1 class="text-2xl font-black tracking-tight text-navy sm:text-3xl">Akaunti</h1>
        <p class="mt-1 text-sm text-gray-500">Wasifu, masomo na mipangilio ya akaunti yako.</p>
    </div>

    @auth
        <div class="mb-6 flex items-center gap-4 rounded-2xl border border-sky-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xl font-black text-primary" aria-hidden="true">
                {{ mb_strtoupper(mb_substr(trim(auth()->user()->name ?: 'U'), 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-base font-extrabold text-navy">{{ auth()->user()->name }}</p>
                <p class="break-all text-sm text-gray-500">{{ auth()->user()->email }}</p>
                <span class="mt-1 inline-flex rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-primary">Akaunti yangu</span>
            </div>
        </div>

        <section aria-labelledby="account-actions-heading" class="mb-6">
            <h2 id="account-actions-heading" class="mb-3 text-xs font-extrabold uppercase tracking-wider text-gray-500">Akaunti na Masomo</h2>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <a href="{{ route('profile.edit') }}" class="flex min-h-[60px] items-center gap-3 px-4 py-3 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-primary">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    </span>
                    <span class="flex-1 font-semibold text-navy">Wasifu wangu</span>
                    <span class="text-xl text-gray-400" aria-hidden="true">&rsaquo;</span>
                </a>
                <div class="mx-4 border-t border-gray-100"></div>
                <a href="{{ route('dashboard') }}" class="flex min-h-[60px] items-center gap-3 px-4 py-3 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-primary">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 7v14M3 18V5a2 2 0 0 1 2-2h3a4 4 0 0 1 4 4 4 4 0 0 1 4-4h3a2 2 0 0 1 2 2v13h-5a4 4 0 0 0-4 3 4 4 0 0 0-4-3H3Z"/></svg>
                    </span>
                    <span class="flex-1 font-semibold text-navy">Masomo Yangu</span>
                    <span class="text-xl text-gray-400" aria-hidden="true">&rsaquo;</span>
                </a>
            </div>
        </section>
    @else
        <section aria-labelledby="welcome-heading" class="mb-6 rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
            <h2 id="welcome-heading" class="text-lg font-extrabold text-navy">Karibu Uzima Milele</h2>
            <p class="mb-4 mt-1 text-sm leading-6 text-gray-600">Ingia ili kuona masomo yako na maendeleo ya kujifunza.</p>
            <a href="{{ route('login') }}" class="flex min-h-[46px] items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primaryDark">Ingia kwenye akaunti</a>
            <a href="{{ route('register') }}" class="mt-3 flex min-h-[46px] items-center justify-center rounded-xl border border-sky-200 bg-white px-4 py-3 text-sm font-bold text-primary hover:bg-sky-50">Jisajili</a>
            <a href="{{ route('password.request') }}" class="mt-4 block text-center text-sm font-semibold text-primary underline-offset-2 hover:underline">Umesahau nenosiri?</a>
        </section>
    @endauth

    <section aria-labelledby="legal-heading" class="mb-6">
        <h2 id="legal-heading" class="mb-3 text-xs font-extrabold uppercase tracking-wider text-gray-500">Faragha na Taarifa</h2>
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <a href="{{ route('privacy-policy') }}" class="flex min-h-[60px] items-center gap-3 px-4 py-3 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-primary">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 22 7-4V6l-7-4-7 4v12l7 4Z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <span class="flex-1 font-semibold text-navy">Sera ya Faragha</span>
                <span class="text-xl text-gray-400" aria-hidden="true">&rsaquo;</span>
            </a>
            <div class="mx-4 border-t border-gray-100"></div>
            <a href="{{ route('terms-and-conditions') }}" class="flex min-h-[60px] items-center gap-3 px-4 py-3 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-primary">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8l4 4v14H4V3h4Z"/><path d="M16 3v5h4M8 13h8M8 17h6"/></svg>
                </span>
                <span class="flex-1 font-semibold text-navy">Masharti na Vigezo</span>
                <span class="text-xl text-gray-400" aria-hidden="true">&rsaquo;</span>
            </a>
            <div class="mx-4 border-t border-gray-100"></div>
            <a href="{{ route('account-deletion') }}" class="flex min-h-[60px] items-center gap-3 px-4 py-3 transition hover:bg-sky-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block font-semibold text-navy">Kufuta Akaunti</span>
                    <span class="block text-xs text-gray-500">Maelekezo na kuomba kufutwa kwa taarifa</span>
                </span>
                <span class="text-xl text-gray-400" aria-hidden="true">&rsaquo;</span>
            </a>
        </div>
    </section>

    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl border border-red-100 bg-white px-4 py-3 font-bold text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-600">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>
                Toka kwenye akaunti
            </button>
        </form>
    @endauth
</section>
@endsection
