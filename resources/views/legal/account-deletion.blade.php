@extends('layouts.app')

@section('title', 'Kufuta Akaunti | Uzima Milele')

@section('content')
{{-- Full-width hero consistent with the site's Tafakari and About page structure. --}}
<section class="relative min-h-[240px] md:min-h-[330px] flex items-center justify-center overflow-hidden bg-navy text-white">
    <div class="relative z-10 text-center px-4 py-12 max-w-4xl mx-auto">
        <p class="text-accent text-xs sm:text-sm font-black uppercase tracking-wide">UZIMA MILELE</p>
        <h1 class="mt-3 text-3xl md:text-5xl font-black leading-tight">Kufuta Akaunti ya Uzima Milele</h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">Maelekezo ya kuomba kufutwa kwa akaunti na taarifa zako.</p>
        <div class="mx-auto mt-4 h-1 w-12 rounded-full bg-accent"></div>
    </div>
</section>
<section class="bg-gray-50 py-10 sm:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-5 leading-relaxed">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-7">
            <h2 class="text-xl text-navy font-black mb-3">Ukiweza kuingia kwenye akaunti</h2>
            <ol class="list-decimal pl-5 space-y-2 text-gray-700">
                <li>Ingia kwenye akaunti yako ya Uzima Milele.</li>
                <li>Fungua sehemu ya <a class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark" href="{{ route('profile.edit') }}">Wasifu (Profile)</a>.</li>
                <li>Chagua <strong>Delete Account</strong> na fuata hatua za uthibitishaji.</li>
            </ol>
            <p class="text-gray-600 text-sm mt-3">Kwa sasa, uthibitishaji wa kujifutia akaunti unaomba nenosiri.
            Ikiwa ulisajili kwa Google pekee na hujawahi kuweka nenosiri, ondoka kwenye akaunti, fungua ukurasa wa kuingia na chagua “Umesahau nenosiri?” ili kuweka nenosiri kwa kutumia barua pepe yako. Ikiwa hatua hii haiwezekani, tumia mawasiliano hapa chini.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-7">
            <h2 class="text-xl text-navy font-black mb-3">Ikiwa huwezi kuingia</h2>
            <p class="text-gray-700">Tuma ombi la kufuta akaunti kwa
                <a class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark" href="mailto:info@uzimamilele.or.tz?subject=Ombi%20la%20Kufuta%20Akaunti">info@uzimamilele.or.tz</a>.
                Eleza kwamba unaomba kufuta akaunti na taja barua pepe inayohusishwa nayo.
                Timu itahitaji kuthibitisha umiliki wa akaunti kabla ya kuchukua hatua.
                Usitume nenosiri lako kwa barua pepe.</p>
        </div>
        <div class="bg-white border-l-4 border-accent rounded-2xl shadow-sm p-5 sm:p-6 text-sm text-gray-700">
            <strong>Angalizo:</strong> Athari za kufuta akaunti kwa taarifa zinazohusiana na masomo,
            cheti, ujumbe na rekodi zinazohitajika kuhifadhiwa bado zinapitiwa.
            Rekodi za uthibitishaji wa kubadili nenosiri zinazohusiana na barua pepe ya akaunti hufutwa pamoja na akaunti.
            Usajili wa kupokea tafakari kwa barua pepe ni huduma tofauti; kuufuta akaunti hakuondoi
            moja kwa moja usajili huo au historia ya kampeni. Ili kuacha kupokea barua pepe,
            tumia kiungo cha kujiondoa kinachopatikana kwenye ujumbe.
            Hatua hii haimaanishi kwamba rekodi zote zinazohusiana hufutwa mara moja.
        </div>
        <p class="text-sm text-gray-600">Tazama pia <a class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark" href="{{ route('privacy-policy') }}">Sera ya Faragha</a>.</p>
    </div>
</section>
@endsection
