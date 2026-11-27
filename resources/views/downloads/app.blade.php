@extends('layouts.app')
@section('title', 'Pakua Programu ya Uzima Milele')
@section('content')
<section class="max-w-3xl mx-auto px-4 py-12 md:py-20">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 md:p-12 text-center">
        <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="h-24 max-w-full object-contain mx-auto mb-6">
        <p class="text-primary font-bold mb-2">Programu rasmi ya Android</p>
        <h1 class="text-3xl md:text-5xl font-black text-navy mb-4">Pakua Programu ya Uzima Milele</h1>
        <p class="text-gray-600 leading-relaxed mb-8">Soma tafakari, jifunze Biblia na ukue kiroho popote ulipo.</p>
        @if($release)
            <a href="{{ route('app.download.android') }}" class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-4 rounded-xl font-black hover:bg-primaryDark">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/></svg>
                Pakua kwa Android
            </a>
            <p class="mt-4 text-sm text-gray-600">Toleo {{ $release->version }} · {{ number_format($release->size_bytes / 1048576, 1) }} MB · APK</p>
            <p class="mt-1 text-sm text-gray-500">{{ $release->published_at->timezone('Africa/Dar_es_Salaam')->format('d/m/Y') }}</p>
            <p class="mt-3 text-sm text-primary">Pakua kutoka tovuti rasmi.</p>
        @else
            <p class="rounded-xl bg-gray-50 p-4 text-gray-600">Programu haipatikani kwa sasa. Tafadhali tembelea tena baadaye.</p>
        @endif
    </div>
    <div class="mt-6 bg-white border border-gray-200 rounded-2xl p-6 md:p-8">
        <h2 class="text-xl font-black text-navy mb-4">Jinsi ya kusakinisha</h2>
        <ol class="list-decimal pl-6 space-y-3 text-gray-700">
            <li>Bonyeza kitufe cha kupakua programu.</li>
            <li>Fungua faili la APK ulilopakua.</li>
            <li>Ikiombwa, ruhusu usakinishaji kutoka chanzo hiki kwa muda.</li>
            <li>Sakinisha programu, kisha fungua Uzima Milele. Unaweza kuzima ruhusa hiyo baada ya kusakinisha.</li>
        </ol>
        <p class="mt-4 text-sm text-gray-500">Faili hili ni kwa simu za Android pekee; halisakinishwi kwenye iPhone.</p>
    </div>
    @if($release && filled($release->release_notes))
        <div class="mt-6 bg-white border border-gray-200 rounded-2xl p-6 md:p-8">
            <h2 class="text-xl font-black text-navy mb-3">Mabadiliko katika toleo hili</h2>
            <p class="whitespace-pre-wrap text-gray-700">{{ $release->release_notes }}</p>
        </div>
    @endif
    <div class="mt-6 text-center">
        <a class="text-primary font-bold" href="https://wa.me/?text={{ rawurlencode(route('app.download.page')) }}" rel="noopener noreferrer" target="_blank">Shiriki kupitia WhatsApp</a>
    </div>
</section>
@endsection
