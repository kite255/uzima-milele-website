@extends('layouts.app')

@section('title', 'Sera ya Faragha | Uzima Milele')

@section('content')
{{-- Full-width hero consistent with the site's Tafakari and About page structure. --}}
<section class="relative min-h-[240px] md:min-h-[330px] flex items-center justify-center overflow-hidden bg-navy text-white">
    <div class="relative z-10 text-center px-4 py-12 max-w-4xl mx-auto">
        <p class="text-accent text-xs sm:text-sm font-black uppercase tracking-wide">UZIMA MILELE</p>
        <h1 class="mt-3 text-3xl md:text-5xl font-black leading-tight">Sera ya Faragha</h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">Tunathamini faragha yako na usalama wa taarifa zako.</p>
        <div class="mx-auto mt-4 h-1 w-12 rounded-full bg-accent"></div>
    </div>
</section>
<section class="py-10 sm:py-16 bg-gray-50">
    <article class="max-w-5xl mx-auto px-4 sm:px-6 space-y-5 text-gray-700 leading-relaxed">
        <div class="rounded-2xl border-l-4 border-accent bg-white p-5 sm:p-6 text-sm text-gray-700 shadow-sm">
            <strong>Rasimu inayopitiwa kabla ya kuchapishwa kwenye Google Play:</strong>
            Maelezo haya yanaonyesha vipengele vilivyotambuliwa kwenye mfumo wa Uzima Milele. Bado tunathibitisha
            huduma zote za nje za programu ya Median, taratibu za usalama, pamoja na vipindi vya kuhifadhi na kufuta data.
            Sera hii haitachukuliwa kuwa toleo la mwisho mpaka ukaguzi huo ukamilike.
        </div>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">1. Data tunazoshughulikia</h2>
            <p>Huduma za tovuti zinajumuisha akaunti za wanafunzi, majina na anwani za barua pepe, usajili wa masomo,
            maendeleo ya kujifunza, matokeo ya mitihani, vyeti, majibu na maswali ya masomo. Unapowasilisha ombi la maombi au ushuhuda,
            taarifa utakazowasilisha zinaweza kushughulikiwa ili kukuhudumia. Usajili wa tafakari kwa barua pepe
            hutumia jina, barua pepe, namba ya simu ikiwa umeitoa, na mapendeleo yako ya kupokea ujumbe.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">2. Sababu za matumizi</h2>
            <p>Data hutumika kusimamia akaunti, kutoa masomo na tafakari, kufuatilia maendeleo ya mwanafunzi,
            kujibu mawasiliano na kutuma ujumbe ulioombwa au uliokubaliwa.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">3. Huduma zinazohusiana</h2>
            <p>Unaweza kuingia kwa kutumia Google; mfumo hupokea taarifa zinazohitajika kwa kuingia kama jina,
            barua pepe, kitambulisho cha akaunti ya Google na picha ya wasifu ikiwa imetolewa.
            Barua pepe za tafakari na taarifa hutumwa kupitia huduma ya barua pepe iliyosanidiwa na huduma.
            Tovuti hutumia pia huduma ya mazungumzo ya Elfsight na rasilimali za nje za mwonekano wa kurasa.
            Programu ya simu imetengenezwa kwa Median na huonyesha tovuti ndani ya programu.
            Huduma za Median, ruhusa za kifaa, uchanganuzi na taarifa zinazoshughulikiwa na watoa huduma wengine
            bado zinahitaji kuhakikiwa dhidi ya mipangilio halisi ya programu kabla ya kuchapishwa.</p>
        </section>
        <section class="bg-white border border-sky-100 shadow-sm rounded-2xl p-5 sm:p-7">
            <h2 class="font-black text-navy text-xl mb-3">3A. Faragha ya watoto — Uzima Milele Watoto</h2>
            <p>Uzima Milele Watoto hutoa video, hadithi za Biblia na majaribio ya kujifunza. Kurasa za watoto na
            majaribio yake yanaweza kutumiwa bila kufungua akaunti. Watoto hawahitaji kutoa jina, namba ya simu
            au barua pepe ili kutazama masomo hayo na kujibu maswali.</p>
            <p class="mt-3">Mfumo huhifadhi matokeo ya majaribio ya watoto kama alama, idadi ya majibu sahihi
            na somo husika. Katika marekebisho ya sasa, matokeo mapya yanahifadhiwa chini ya jina la jumla
            <strong>Guest</strong>, hata ikiwa mtumiaji ameingia kwenye akaunti. Hata hivyo, rekodi za zamani
            zinaweza kuwa na jina la mtumiaji; ukaguzi wa namna ya kuzifuta au kuzihifadhi bado unaendelea.
            Taarifa za kiufundi kama kumbukumbu za seva zinaweza pia kuzalishwa wakati wa kutumia tovuti.</p>
            <p class="mt-3">Baadhi ya masomo yana video za YouTube zilizopachikwa. Tumeanza kutumia
            <strong>youtube-nocookie.com</strong> kwa uchezaji wa video, lakini hii haimaanishi kuwa YouTube
            haishughulikii taarifa zozote za matumizi. Kutazama video kunaweza kuhusisha uchakataji wa data
            na huduma ya YouTube. Pia tunakagua viungo vinavyompeleka mtumiaji nje ya tovuti,
            huduma nyingine za ukurasa na vipengele vya programu ya Median.</p>
            <p class="mt-3">Kwa mzazi au mlezi mwenye swali kuhusu faragha ya mtoto au anayetaka
            kuomba kufutwa kwa taarifa zinazoweza kumhusu mtoto, wasiliana na
            <a href="mailto:info@uzimamilele.or.tz?subject=Faragha%20ya%20Watoto"
               class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark">info@uzimamilele.or.tz</a>.
            Tutahitaji kuthibitisha uhusiano na mamlaka ya mwombaji kabla ya kushughulikia taarifa binafsi.
            Taarifa za muda wa uhifadhi, matumizi ya huduma za nje na taratibu kamili za maombi bado
            zinafanyiwa ukaguzi kabla ya sera hii kuidhinishwa.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">4. Usalama na uhifadhi wa taarifa</h2>
            <p>Taarifa za akaunti na masomo huhifadhiwa ili kuendesha huduma zinazohusiana.
            Baadhi ya rekodi kama historia ya kampeni za barua pepe, maombi, ushuhuda, nakala za hifadhi na kumbukumbu
            za mfumo zinaweza kushughulikiwa tofauti na akaunti ya mtumiaji. Muda mahususi wa kuhifadhi taarifa,
            ufutaji wa nakala za hifadhi na udhibiti wa ufikiaji bado vinakaguliwa.
            Hatutadai kwamba taarifa zote hufutwa mara moja wakati masharti hayo hayajathibitishwa.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">5. Haki na maombi ya kufuta akaunti</h2>
            <p>Mtumiaji aliyeingia anaweza kuomba kufuta akaunti kupitia sehemu ya Wasifu.
            Kufuta akaunti na kujiondoa kwenye orodha ya barua pepe ni michakato tofauti.
            Unaweza kujiondoa kwenye barua pepe kwa kutumia kiungo cha kujiondoa kilicho kwenye ujumbe.
            Kwa maelezo ya kufuta akaunti, tembelea
                <a href="{{ route('account-deletion') }}" class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark">Utaratibu wa Kufuta Akaunti</a>.
                Unaweza pia kuwasiliana na <a href="mailto:info@uzimamilele.or.tz" class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark">info@uzimamilele.or.tz</a>
                kwa maswali kuhusu taarifa zako.</p>
        </section>
        <section class="bg-white border border-gray-100 shadow-sm rounded-2xl p-5 sm:p-7"><h2 class="font-black text-navy text-xl mb-3">6. Mabadiliko ya sera</h2>
            <p>Sera hii itasasishwa pale huduma, watoa huduma, au namna ya kushughulikia data inapobadilika.
            Toleo la mwisho litahitaji kukaguliwa na kuthibitishwa na uongozi wa Uzima Milele kabla ya kutumika
            kama sera rasmi kwenye Google Play au Apple App Store.</p>
        </section>
    </article>
</section>
@endsection
