@extends('layouts.app')
@section('title', 'Pakua Uzima Milele')
@section('content')
<section class="max-w-4xl mx-auto px-4 py-6 md:py-12">
    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="h-1.5 bg-primary"></div>
        <div class="p-5 sm:p-8 md:p-10">
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 shrink-0 rounded-2xl border border-gray-100 bg-white flex items-center justify-center p-2">
                    <img src="{{ asset('logo.png') }}" alt="Nembo ya Uzima Milele" class="w-full h-full object-contain">
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-primary">Programu rasmi</p>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight text-navy">Uzima Milele</h1>
                    <p class="mt-1 text-sm text-gray-500">Android &middot; APK</p>
                </div>
            </div>
            <p class="mt-5 text-gray-600 leading-relaxed">Jifunze Biblia, soma tafakari za kila siku na ukue kiroho popote ulipo.</p>
            @if($release)
                <dl class="mt-6 grid grid-cols-3 divide-x divide-gray-200 rounded-xl bg-gray-50 py-4 text-center">
                    <div class="px-1"><dt class="text-xs text-gray-500">Toleo</dt><dd class="mt-1 font-bold text-navy break-all">{{ $release->version }}</dd></div>
                    <div class="px-1"><dt class="text-xs text-gray-500">Ukubwa</dt><dd class="mt-1 font-bold text-navy">{{ number_format($release->size_bytes / 1048576, 1) }} MB</dd></div>
                    <div class="px-1"><dt class="text-xs text-gray-500">Imetolewa</dt><dd class="mt-1 text-sm font-bold text-navy">{{ $release->published_at->timezone('Africa/Dar_es_Salaam')->format('d/m/Y') }}</dd></div>
                </dl>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('app.download.android') }}" class="flex flex-1 items-center justify-center gap-2 bg-primary text-white px-5 py-4 rounded-xl font-bold hover:bg-primaryDark focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/></svg>
                        Pakua kwa Android
                    </a>
                    <a href="https://wa.me/?text={{ rawurlencode(route('app.download.page')) }}" rel="noopener noreferrer" target="_blank" class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-4 font-bold text-navy hover:bg-gray-50">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m9 10 6-4m-6 8 6 4"/></svg>
                        Shiriki
                    </a>
                </div>
                <p class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></svg>
                    Pakua kutoka tovuti rasmi.
                </p>
            @else
                <p class="mt-5 rounded-xl bg-gray-50 p-4 text-gray-600">Programu haipatikani kwa sasa. Tafadhali tembelea tena baadaye.</p>
            @endif
        </div>
    </div>

    <div class="mt-5 grid md:grid-cols-2 gap-5">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-black text-navy mb-4">Vipengele vya programu</h2>
            <div class="grid grid-cols-2 gap-3 text-sm font-bold text-navy">
                @foreach(['Masomo ya Biblia', 'Tafakari', 'Watoto', 'Mtumishi'] as $feature)
                    <div class="rounded-lg bg-gray-50 px-3 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        {{ $feature }}
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-gray-500">Kwa simu za Android pekee. APK haisakinishwi kwenye iPhone.</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-black text-navy mb-4">Jinsi ya kusakinisha</h2>
            <ol class="space-y-3 text-sm text-gray-700">
                @foreach(['Bonyeza Pakua kwa Android.', 'Fungua faili la APK ulilopakua.', 'Ikiombwa, ruhusu usakinishaji kutoka chanzo hiki kwa muda.', 'Sakinisha na ufungue Uzima Milele. Zima ruhusa hiyo baada ya kusakinisha.'] as $step)
                    <li class="flex items-start gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">{{ $loop->iteration }}</span><span class="pt-0.5">{{ $step }}</span></li>
                @endforeach
            </ol>
        </div>
    </div>
    @if($release && filled($release->release_notes))
        <details class="mt-5 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <summary class="cursor-pointer font-bold text-navy">Mabadiliko katika toleo hili</summary>
            <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $release->release_notes }}</p>
        </details>
    @endif
</section>
@endsection
