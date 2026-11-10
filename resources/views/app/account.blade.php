@extends('layouts.app')

@section('title', 'Akaunti - Uzima Milele')

@section('content')
<section class="mx-auto w-full max-w-3xl px-4 py-8 sm:py-12">
    <h1 class="mb-2 text-2xl font-black text-navy">Akaunti</h1>
    <p class="mb-6 text-sm text-gray-600">Dhibiti akaunti yako na taarifa za kuingia.</p>

    @auth
        <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-5">
            <p class="font-bold text-navy">{{ auth()->user()->name }}</p>
            <p class="mt-1 break-all text-sm text-gray-500">{{ auth()->user()->email }}</p>
        </div>
        <div class="space-y-3">
            <a href="{{ route('profile.edit') }}" class="block rounded-xl border border-gray-200 bg-white p-4 font-semibold text-primary">Wasifu wangu</a>
            <a href="{{ route('dashboard') }}" class="block rounded-xl border border-gray-200 bg-white p-4 font-semibold text-primary">Masomo Yangu</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-gray-200 bg-white p-4 text-left font-semibold text-red-700">Toka kwenye akaunti</button>
            </form>
        </div>
    @else
        <div class="space-y-3">
            <a href="{{ route('login') }}" class="block rounded-xl bg-primary p-4 text-center font-bold text-white">Ingia kwenye akaunti</a>
            <a href="{{ route('register') }}" class="block rounded-xl border border-gray-200 bg-white p-4 text-center font-bold text-primary">Jisajili</a>
            <a href="{{ route('password.request') }}" class="block rounded-xl border border-gray-200 bg-white p-4 text-center font-bold text-primary">Umesahau nenosiri?</a>
        </div>
        <p class="mt-5 text-sm leading-6 text-gray-600">Uliingia kwa kutumia Google hapo awali? Tumia barua pepe ileile kuomba kiungo cha kuweka nenosiri la Uzima Milele.</p>
    @endauth

    <section aria-label="Faragha na taarifa" class="mt-8">
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Faragha na Taarifa</h2>
        <a href="{{ route('privacy-policy') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 font-semibold text-primary">
            <span>Sera ya Faragha</span>
            <span aria-hidden="true">&rsaquo;</span>
        </a>
        <a href="{{ route('terms-and-conditions') }}" class="mt-3 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 font-semibold text-primary">
            <span>Masharti na Vigezo</span>
            <span aria-hidden="true">&rsaquo;</span>
        </a>
    </section>
</section>
@endsection
