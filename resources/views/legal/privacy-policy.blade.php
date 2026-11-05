@extends('layouts.app')

@section('title', 'Sera ya Faragha | Uzima Milele')

@section('content')
{{-- Compact shared visual language with the Tafakari page; logo stays in the site navbar. --}}
<section class="relative min-h-[220px] sm:min-h-[260px] flex items-center justify-center overflow-hidden bg-navy">
    <img
        src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600&auto=format&fit=crop"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 h-full w-full object-cover"
    >
    <div class="absolute inset-0 bg-navy/75"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 py-10 text-center text-white">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight">Sera ya Faragha</h1>
        <p class="mt-4 text-sm sm:text-base text-white/90 leading-relaxed max-w-2xl mx-auto">Tunathamini faragha yako na usalama wa taarifa zako.</p>
        <div class="mt-5 mx-auto h-1 w-16 rounded-full bg-accent"></div>
    </div>
</section>
<section class="py-10 sm:py-16 bg-gray-50">
    <article class="max-w-5xl mx-auto px-4 sm:px-6 space-y-5 text-gray-700 leading-relaxed">
        <div class="rounded-2xl border-l-4 border-accent bg-white p-5 sm:p-6 text-sm text-gray-700 shadow-sm">
            <strong>Rasimu kwa ajili ya ukaguzi:</strong> Kabla ya kuchapisha programu, huduma inapaswa kuthibitisha
            aina zote za data zinazokusanywa, watoa huduma wa nje, vipindi vya kuhifadhi data na taratibu za maombi.
        </div>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">1. Data tunazoshughulikia</h2>
            <p>Huduma za tovuti zinajumuisha akaunti za wanafunzi, majina na anwani za barua pepe, usajili wa masomo,
            maendeleo ya kujifunza, majibu na maswali ya masomo. Unapowasilisha ombi la maombi au ushuhuda,
            taarifa utakazowasilisha zinaweza kushughulikiwa ili kukuhudumia. Usajili wa tafakari kwa barua pepe
            hutumia taarifa unazowasilisha na mapendeleo yako ya kupokea ujumbe.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">2. Sababu za matumizi</h2>
            <p>Data hutumika kusimamia akaunti, kutoa masomo na tafakari, kufuatilia maendeleo ya mwanafunzi,
            kujibu mawasiliano na kutuma ujumbe ulioombwa au uliokubaliwa.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">3. Huduma zinazohusiana</h2>
            <p>Baadhi ya vipengele hutegemea huduma za nje, kwa mfano kuingia kupitia Google na huduma za barua pepe.
            Taarifa mahususi za watoa huduma, uchanganuzi na programu za simu zihakikiwe kabla ya toleo la umma.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">4. Usalama na uhifadhi wa taarifa</h2>
            <p>Uzima Milele inapaswa kuweka hatua za usalama na kufafanua muda wa kuhifadhi taarifa kulingana
            na matumizi na majukumu ya kisheria. Hatua na muda mahususi vinahitaji kuthibitishwa kabla ya kuchapishwa.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">5. Haki na maombi ya kufuta akaunti</h2>
            <p>Kwa maelezo ya kufuta akaunti, tembelea
                <a href="{{ route('account-deletion') }}" class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark">Utaratibu wa Kufuta Akaunti</a>.
                Unaweza pia kuwasiliana na <a href="mailto:info@uzimamilele.or.tz" class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark">info@uzimamilele.or.tz</a>
                kwa maswali kuhusu taarifa zako.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">6. Mabadiliko ya sera</h2>
            <p>Sera hii itasasishwa pale utaratibu wa matumizi ya taarifa unapobadilika. Toleo la mwisho lazima
            lipitiwe na kuthibitishwa na uongozi wa huduma kabla ya kuwasilishwa kwenye maduka ya programu.</p>
        </section>
    </article>
</section>
@endsection
