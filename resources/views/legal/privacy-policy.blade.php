@extends('layouts.app')

@section('title', 'Sera ya Faragha | Uzima Milele')

@section('content')
<section class="bg-navy text-white py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl sm:text-4xl font-black">Sera ya Faragha</h1>
        <p class="mt-3 text-white/80">Uzima Milele Ministry — Taarifa kuhusu matumizi ya data binafsi.</p>
    </div>
</section>
<section class="py-12 bg-gray-50">
    <article class="max-w-4xl mx-auto px-4 space-y-6 text-gray-800 leading-relaxed">
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm">
            <strong>Rasimu kwa ajili ya ukaguzi:</strong> Kabla ya kuchapisha programu, huduma inapaswa kuthibitisha
            aina zote za data zinazokusanywa, watoa huduma wa nje, vipindi vya kuhifadhi data na taratibu za maombi.
        </div>
        <section><h2 class="font-bold text-xl mb-2">1. Data tunazoshughulikia</h2>
            <p>Huduma za tovuti zinajumuisha akaunti za wanafunzi, majina na anwani za barua pepe, usajili wa masomo,
            maendeleo ya kujifunza, majibu na maswali ya masomo. Unapowasilisha ombi la maombi au ushuhuda,
            taarifa utakazowasilisha zinaweza kushughulikiwa ili kukuhudumia. Usajili wa tafakari kwa barua pepe
            hutumia taarifa unazowasilisha na mapendeleo yako ya kupokea ujumbe.</p>
        </section>
        <section><h2 class="font-bold text-xl mb-2">2. Sababu za matumizi</h2>
            <p>Data hutumika kusimamia akaunti, kutoa masomo na tafakari, kufuatilia maendeleo ya mwanafunzi,
            kujibu mawasiliano na kutuma ujumbe ulioombwa au uliokubaliwa.</p>
        </section>
        <section><h2 class="font-bold text-xl mb-2">3. Huduma zinazohusiana</h2>
            <p>Baadhi ya vipengele hutegemea huduma za nje, kwa mfano kuingia kupitia Google na huduma za barua pepe.
            Taarifa mahususi za watoa huduma, uchanganuzi na programu za simu zihakikiwe kabla ya toleo la umma.</p>
        </section>
        <section><h2 class="font-bold text-xl mb-2">4. Usalama na uhifadhi wa taarifa</h2>
            <p>Uzima Milele inapaswa kuweka hatua za usalama na kufafanua muda wa kuhifadhi taarifa kulingana
            na matumizi na majukumu ya kisheria. Hatua na muda mahususi vinahitaji kuthibitishwa kabla ya kuchapishwa.</p>
        </section>
        <section><h2 class="font-bold text-xl mb-2">5. Haki na maombi ya kufuta akaunti</h2>
            <p>Kwa maelezo ya kufuta akaunti, tembelea
                <a href="{{ route('account-deletion') }}" class="text-primary underline">Utaratibu wa Kufuta Akaunti</a>.
                Unaweza pia kuwasiliana na <a href="mailto:info@uzimamilele.or.tz" class="text-primary underline">info@uzimamilele.or.tz</a>
                kwa maswali kuhusu taarifa zako.</p>
        </section>
        <section><h2 class="font-bold text-xl mb-2">6. Mabadiliko ya sera</h2>
            <p>Sera hii itasasishwa pale utaratibu wa matumizi ya taarifa unapobadilika. Toleo la mwisho lazima
            lipitiwe na kuthibitishwa na uongozi wa huduma kabla ya kuwasilishwa kwenye maduka ya programu.</p>
        </section>
    </article>
</section>
@endsection
