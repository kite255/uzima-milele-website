@extends('layouts.app')

@section('title', 'Sera ya Faragha | Uzima Milele')

@section('content')
<section class="relative flex min-h-[240px] items-center justify-center overflow-hidden bg-navy text-white md:min-h-[330px]">
    <div class="relative z-10 mx-auto max-w-4xl px-4 py-12 text-center">
        <p class="text-xs font-black uppercase tracking-wide text-accent sm:text-sm">UZIMA MILELE</p>
        <h1 class="mt-3 text-3xl font-black leading-tight md:text-5xl">Sera ya Faragha</h1>
        <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-white/90 sm:text-base">
            Taarifa kuhusu faragha ya wanafunzi, familia na watumiaji wa Uzima Milele Watoto.
        </p>
        <div class="mx-auto mt-4 h-1 w-12 rounded-full bg-accent"></div>
    </div>
</section>
<section class="bg-gray-50 py-10 sm:py-16">
    <article class="mx-auto max-w-5xl space-y-5 px-4 leading-relaxed text-gray-700 sm:px-6">
        <nav aria-label="Sehemu za sera ya faragha" class="rounded-2xl border border-sky-100 bg-sky-50 p-5 text-sm sm:p-6">
            <h2 class="mb-3 font-black text-navy">Soma sehemu muhimu</h2>
            <div class="flex flex-wrap gap-x-6 gap-y-3">
                <a href="#faragha-ya-watoto" class="font-semibold text-primary underline underline-offset-2">Faragha ya Watoto</a>
                <a href="#hifadhi-na-usalama" class="font-semibold text-primary underline underline-offset-2">Usalama na Uhifadhi</a>
                <a href="#haki-na-ufutaji" class="font-semibold text-primary underline underline-offset-2">Kufuta Akaunti na Data</a>
                <a href="#mawasiliano" class="font-semibold text-primary underline underline-offset-2">Mawasiliano</a>
            </div>
        </nav>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">1. Taarifa tunazoshughulikia</h2>
            <p>Kwa watumiaji wanaojisajili, mfumo unaweza kushughulikia jina, barua pepe, namba ya simu
            ikiwa imetolewa, taarifa za kuingia, usajili wa masomo, maendeleo ya kujifunza, majibu na matokeo
            ya majaribio pamoja na vyeti. Ukituma ombi la maombi, ushuhuda au ujumbe, maelezo uliyoandika
            yanaweza kuhifadhiwa ili kushughulikia ombi lako. Usajili wa tafakari kwa barua pepe
            unaweza kutumia jina, barua pepe, namba ya simu uliyoitoa na mapendeleo ya mawasiliano.</p>
            <p class="mt-3">Mifumo ya tovuti na seva pia inaweza kuzalisha taarifa za kiufundi, kama
            kumbukumbu za maombi na usalama. Taarifa zinazosindikwa na programu ya simu au huduma za nje
            zinahitaji kuhakikiwa dhidi ya usanidi wa mwisho kabla ya kutolewa kwa programu.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">2. Sababu za kutumia taarifa</h2>
            <p>Taarifa hutumiwa kuendesha akaunti na usalama wa huduma, kutoa masomo ya Biblia,
            kuonyesha maendeleo na matokeo ya kujifunza, kushughulikia maombi na ushuhuda,
            na kutuma tafakari au ujumbe unaohusiana na huduma pale inapofaa.
            Uchakataji wowote wa ziada wa taarifa lazima uendane na huduma halisi na maelezo ya sera hii.</p>
        </section>

        <section id="faragha-ya-watoto" class="scroll-mt-24 rounded-2xl border border-sky-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">3. Faragha ya watoto — Uzima Milele Watoto</h2>
            <p>Uzima Milele Watoto hutoa video, hadithi na majaribio ya Biblia. Kurasa za Watoto
            na majaribio yake zinaweza kutumiwa bila kujisajili. Mtoto hahitaji kuandika jina,
            barua pepe au namba ya simu ili kutazama somo au kufanya jaribio la Watoto.</p>
            <p class="mt-3">Majaribio yanahifadhi somo husika, alama, idadi ya majibu sahihi na idadi
            ya maswali. Katika marekebisho ya programu yaliyowekwa kwenye hatua ya majaribio,
            matokeo mapya hayahifadhi jina la akaunti, bali hutumia jina la jumla
            <strong>Guest</strong>. Hata hivyo, baadhi ya rekodi zilizotengenezwa kabla ya marekebisho
            hayo zinaweza kuwa na majina; zinahitaji ukaguzi wa uhifadhi na ufutaji.</p>
            <p class="mt-3">Video zilizopachikwa zinatumia huduma ya YouTube. Toleo la marekebisho
            hutumia anwani ya <strong>youtube-nocookie.com</strong> kupunguza baadhi ya aina za ufuatiliaji,
            lakini hali hiyo haizuii YouTube au huduma nyingine kuchakata taarifa zote za matumizi.
            Watumiaji wanaweza pia kufuata viungo vinavyoelekea nje ya tovuti. Tunahitaji kuhakiki
            video, viungo, SDK na huduma nyingine zinazofikika kwa watoto kwenye programu ya mwisho.</p>
            <p class="mt-3">Mzazi au mlezi anaweza kuomba ufafanuzi, marekebisho au kufutwa kwa
            taarifa zinazoweza kumhusu mtoto kupitia anwani iliyo chini ya sera hii.
            Ombi la kufuta taarifa binafsi litahitaji uthibitisho unaofaa wa mamlaka ya mwombaji;
            hatuombi nywila za watoto au wazazi kupitia barua pepe.</p>
            <p class="mt-3">Sehemu hii haimaanishi kuwa usajili wa watoto chini ya umri wa kisheria
            na idhini ya wazazi tayari vimetekelezwa. Vipengele vya akaunti, usimamizi wa mzazi
            na huduma za nje lazima vikaguliwe kulingana na hadhira inayolengwa.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">4. Huduma na watoa huduma wengine</h2>
            <p>Ikiwa mtumiaji atachagua kuingia kwa Google, mfumo unaweza kupata kitambulisho cha
            Google, jina, barua pepe na picha ya wasifu inayorejeshwa na huduma hiyo.
            Barua pepe za tafakari na taarifa husafirishwa kupitia huduma iliyosanidiwa kwa barua pepe.
            Tovuti pia ina vipengele vinavyotumia Elfsight na rasilimali za nje.
            Programu ya simu inatengenezwa kupitia Median. Huduma za Median, SDK zake,
            vitambulisho vya kifaa, uchanganuzi, ruhusa na huduma za mazungumzo lazima
            zikaguliwe katika programu iliyojengwa kabla ya kueleza kwa uhakika data
            zinazopokelewa na kila mhusika.</p>
        </section>

        <section id="hifadhi-na-usalama" class="scroll-mt-24 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">5. Usalama na uhifadhi wa taarifa</h2>
            <p>Uzima Milele hutumia akaunti na utaratibu wa uthibitishaji wa kuingia
            ili kudhibiti ufikiaji wa baadhi ya huduma. Hata hivyo, taratibu za udhibiti wa
            ruhusa, hifadhi nakala, kumbukumbu za seva na ufutaji wa data bado zinahitaji
            kuthibitishwa kwa mazingira ya uzalishaji.</p>
            <p class="mt-3">Rekodi za masomo, kampeni za barua pepe, maombi, ushuhuda,
            kumbukumbu za kiufundi na nakala za hifadhi zinaweza kuwa na mifumo
            tofauti ya uhifadhi. Vipindi vya uhifadhi na sababu za kubakiza
            taarifa zinazohitajika bado vinafanyiwa ukaguzi. Hatutadai kwamba
            kila data inafutika mara moja bila uthibitisho huo.</p>
        </section>

        <section id="haki-na-ufutaji" class="scroll-mt-24 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">6. Haki za mtumiaji na kufuta akaunti</h2>
            <p>Mtumiaji anaweza kubadili baadhi ya taarifa zake kupitia Wasifu,
            na kuanzisha ufutaji wa akaunti kwa kuthibitisha nenosiri.
            Pia kuna njia ya kuomba kufuta akaunti ikiwa hawezi kuingia.
            Omba maelekezo kupitia
            <a href="{{ route('account-deletion') }}" class="font-semibold text-primary underline underline-offset-2 hover:text-primaryDark">Utaratibu wa Kufuta Akaunti</a>.
            Ufutaji wa akaunti unaweza kuathiri masomo, matokeo na vyeti;
            namna ya kushughulikia data nyingine zinazojitegemea bado inakaguliwa.</p>
            <p class="mt-3">Usajili wa kupokea tafakari kwa barua pepe ni huduma tofauti na akaunti.
            Ili kuacha kupokea ujumbe huo, tumia kiungo cha kujiondoa katika barua pepe husika.
            Ombi la kuondoa data ya usajili wa tafakari au taarifa nyingine
            linaweza kutumwa kupitia anwani ya mawasiliano hapa chini.</p>
        </section>

        <section id="mawasiliano" class="scroll-mt-24 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">7. Mawasiliano ya faragha</h2>
            <p>Kwa maswali, ombi la kufuta data, au mawasiliano ya mzazi na mlezi,
            wasiliana na Uzima Milele kwa
            <a href="mailto:info@uzimamilele.or.tz" class="font-semibold text-primary underline underline-offset-2 hover:text-primaryDark">info@uzimamilele.or.tz</a>.
            Usitume nenosiri, namba za siri au nyaraka nyeti bila kuombwa kwa njia salama.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="mb-3 text-xl font-black text-navy">8. Mabadiliko ya sera</h2>
            <p>Sera hii itasasishwa pale vipengele vya huduma, watoa huduma au utaratibu wa
            kushughulikia data unapobadilika. Kabla ya kuiidhinisha kama sera rasmi
            ya Google Play, Uzima Milele inahitaji kuthibitisha usahihi wa taarifa,
            kuondoa mapengo yaliyotajwa katika rasimu hii na kupata idhini ya uongozi.</p>
        </section>
    </article>
</section>
@endsection
