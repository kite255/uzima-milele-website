@extends('layouts.app')

@section('title', 'Wasifu na Mipangilio ya Akaunti | Uzima Milele')

@section('content')
<section class="min-h-screen bg-slate-50 py-8 sm:py-12">
    <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6">
        <header class="relative overflow-hidden rounded-3xl bg-navy px-5 py-7 text-white shadow-sm sm:px-9 sm:py-9">
            <div class="pointer-events-none absolute -right-14 -top-16 h-52 w-52 rounded-full border border-white/10"></div>
            <div class="pointer-events-none absolute -bottom-24 right-14 h-48 w-48 rounded-full bg-primary/20"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest text-accent">Akaunti yangu</p>
                    <h1 class="mt-1 text-2xl font-black leading-tight sm:text-3xl">Wasifu na Mipangilio ya Akaunti</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/80">Dhibiti taarifa zako binafsi, usalama na mipangilio ya akaunti sehemu moja.</p>
                </div>
            </div>
        </header>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-3xl border border-red-200 bg-white p-5 shadow-sm sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</section>
@endsection
