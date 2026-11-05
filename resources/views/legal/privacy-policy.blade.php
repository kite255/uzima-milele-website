@extends('layouts.app')

@section('title', 'Sera ya Faragha | Uzima Milele')

@section('content')
<section class="bg-gradient-to-br from-primary via-primaryDark to-navy text-white py-12 sm:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-white rounded-2xl p-2 shadow-sm shrink-0">
                <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="h-12 w-auto object-contain">
            </div>
            <div>
                <p class="font-black text-lg">Uzima Milele</p>
                <p class="text-white/75 text-sm">Huduma ya Neno la Mungu</p>
            </div>
        </div>
        <span class="inline-flex rounded-full bg-white/15 px-4 py-1.5 text-sm font-bold">Faragha na Usalama</span>
        <h1 class="mt-4 text-3xl sm:text-5xl font-black leading-tight">Sera ya Faragha</h1>
        <p class="mt-4 text-white/85 max-w-2xl leading-relaxed">Uzima Milele Ministry — Taarifa kuhusu matumizi ya data binafsi.</p>
        <div class="mt-6 h-1 w-20 bg-accent rounded-full"></div>
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
