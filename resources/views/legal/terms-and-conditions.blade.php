@extends('layouts.app')

@section('title', 'Masharti na Vigezo vya Matumizi | Uzima Milele')

@section('content')
<section class="bg-navy px-4 py-12 text-center text-white sm:py-16">
    <div class="mx-auto max-w-4xl">
        <p class="text-xs font-black uppercase tracking-wide text-accent">UZIMA MILELE</p>
        <h1 class="mt-3 text-3xl font-black sm:text-5xl">Masharti na Vigezo vya Matumizi</h1>
        <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
            Taarifa kuhusu matumizi ya tovuti na programu ya Uzima Milele.
        </p>
    </div>
</section>

<section class="bg-gray-50 py-10 sm:py-16">
    <article class="mx-auto max-w-5xl space-y-5 px-4 text-gray-700 sm:px-6">
        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">1. Kuhusu huduma</h2>
            <p>Uzima Milele hutoa maudhui ya Biblia, tafakari, masomo, majaribio na huduma
            zinazohusiana na kujifunza na kukua kiroho kupitia tovuti na programu ya simu.
            Baadhi ya huduma zinahitaji akaunti; Uzima Milele Watoto ina masomo na majaribio
            yanayoweza kutumiwa bila kujisajili.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">2. Matumizi yanayokubalika</h2>
            <p>Mtumiaji anapaswa kutumia huduma kwa njia inayoheshimu wengine, usalama wa
            mfumo na madhumuni ya elimu. Hairuhusiwi kujaribu kuingia kwenye akaunti za watu
            wengine bila ruhusa, kuvuruga huduma, kutuma taarifa za udanganyifu au kutumia
            huduma kuwanyanyasa watu wengine.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">3. Akaunti na usalama</h2>
            <p>Unapofungua akaunti, toa taarifa sahihi zinazohitajika na linda nenosiri lako.
            Usishiriki nenosiri na mtu mwingine. Ikiwa unaamini akaunti yako imetumiwa bila
            ruhusa, wasiliana na timu ya Uzima Milele. Baadhi ya huduma zinaweza kupatikana
            bila akaunti.</p>
        </section>

        <section class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">4. Uzima Milele Watoto na wazazi</h2>
            <p>Sehemu ya Watoto imekusudiwa kutoa maudhui ya Biblia yanayoeleweka kwa watoto.
            Wazazi na walezi wanahimizwa kuongoza matumizi ya huduma hii, hasa pale mtoto
            anapofungua video au kiungo cha tovuti nyingine. Matumizi ya akaunti, vipengele
            vya mawasiliano na watoa huduma wengine kwa watoto yanahitaji kuendana na
            hatua za ulinzi wa watoto zinazotumika katika huduma.</p>
            <p class="mt-3">Kwa maelezo kuhusu taarifa za watoto, soma
            <a href="{{ route('privacy-policy') }}#faragha-ya-watoto"
               class="font-semibold text-primary underline underline-offset-2">Faragha ya Watoto</a>.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">5. Maudhui na haki za matumizi</h2>
            <p>Maudhui ya masomo na maandishi yanapaswa kutumiwa kwa madhumuni
            yanayoruhusiwa. Baadhi ya video na rasilimali zinatoka kwa watoa huduma
            wengine, hivyo zinaweza kuwa chini ya masharti na haki za wamiliki wao.
            Usisambaze upya maudhui ambayo huna ruhusa ya kuyatumia.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">6. Huduma za nje na upatikanaji</h2>
            <p>Uzima Milele inaweza kuonyesha video za YouTube, kutumia Google kuingia,
            na huduma za nje za mawasiliano au programu ya simu. Huduma hizo zinaweza kuwa
            na masharti yake. Upatikanaji wa baadhi ya vipengele unaweza kubadilika kutokana
            na matengenezo, hitilafu au mabadiliko ya huduma za nje.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">7. Faragha na kufuta akaunti</h2>
            <p>Taarifa binafsi zinahusika na
            <a href="{{ route('privacy-policy') }}" class="font-semibold text-primary underline underline-offset-2">Sera ya Faragha</a>.
            Mtumiaji anaweza kupata maelekezo ya kuomba kufuta akaunti na taarifa
            zinazohusiana nayo kupitia
            <a href="{{ route('account-deletion') }}" class="font-semibold text-primary underline underline-offset-2">Utaratibu wa Kufuta Akaunti</a>.
            Ufafanuzi kuhusu taarifa zinazoweza kuhifadhiwa baada ya kufuta akaunti
            utahitaji kuendana na taratibu halisi za uhifadhi wa data.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">8. Mabadiliko na mawasiliano</h2>
            <p>Masharti haya yanaweza kusasishwa kadiri huduma inavyobadilika. Kwa maswali kuhusu huduma au masharti yake, wasiliana na <a href="mailto:info@uzimamilele.or.tz" class="font-semibold text-primary underline underline-offset-2">info@uzimamilele.or.tz</a>.</p>
        </section>
    </article>
</section>
@endsection
