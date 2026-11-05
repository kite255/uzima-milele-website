@extends('layouts.app')

@section('title', 'Kufuta Akaunti | Uzima Milele')

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
        <span class="inline-flex rounded-full bg-white/15 px-4 py-1.5 text-sm font-bold">Usimamizi wa Akaunti</span>
        <h1 class="mt-4 text-3xl sm:text-5xl font-black leading-tight">Kufuta Akaunti ya Uzima Milele</h1>
        <p class="mt-4 text-white/85 max-w-2xl leading-relaxed">Maelekezo ya kuomba kufutwa kwa akaunti na taarifa zako.</p>
        <div class="mt-6 h-1 w-20 bg-accent rounded-full"></div>
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
            Hatua hii haimaanishi kwamba rekodi zote zinazohusiana hufutwa mara moja.
        </div>
        <p class="text-sm text-gray-600">Tazama pia <a class="text-primary font-semibold underline underline-offset-2 hover:text-primaryDark" href="{{ route('privacy-policy') }}">Sera ya Faragha</a>.</p>
    </div>
</section>
@endsection
