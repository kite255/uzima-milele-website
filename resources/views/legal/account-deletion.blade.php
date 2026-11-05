@extends('layouts.app')

@section('title', 'Kufuta Akaunti | Uzima Milele')

@section('content')
<section class="bg-navy text-white py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl sm:text-4xl font-black">Kufuta Akaunti ya Uzima Milele</h1>
        <p class="mt-3 text-white/80">Maelekezo ya kuomba kufutwa kwa akaunti na taarifa zako.</p>
    </div>
</section>
<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 space-y-6 leading-relaxed">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-xl text-navy font-bold mb-2">Ukiweza kuingia kwenye akaunti</h2>
            <ol class="list-decimal pl-5 space-y-2 text-gray-700">
                <li>Ingia kwenye akaunti yako ya Uzima Milele.</li>
                <li>Fungua sehemu ya <a class="text-primary underline" href="{{ route('profile.edit') }}">Wasifu (Profile)</a>.</li>
                <li>Chagua <strong>Delete Account</strong> na fuata hatua za uthibitishaji.</li>
            </ol>
            <p class="text-gray-600 text-sm mt-3">Kwa sasa, uthibitishaji wa kujifutia akaunti unaomba nenosiri.
            Ikiwa ulisajili kwa Google pekee na huna nenosiri, tumia mawasiliano hapa chini.</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-xl text-navy font-bold mb-2">Ikiwa huwezi kuingia</h2>
            <p class="text-gray-700">Tuma ombi la kufuta akaunti kwa
                <a class="text-primary underline" href="mailto:info@uzimamilele.or.tz?subject=Ombi%20la%20Kufuta%20Akaunti">info@uzimamilele.or.tz</a>.
                Eleza kwamba unaomba kufuta akaunti na taja barua pepe inayohusishwa nayo.
                Timu itahitaji kuthibitisha umiliki wa akaunti kabla ya kuchukua hatua.
                Usitume nenosiri lako kwa barua pepe.</p>
        </div>
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 text-sm text-amber-950">
            <strong>Angalizo:</strong> Athari za kufuta akaunti kwa taarifa zinazohusiana na masomo,
            cheti, ujumbe na rekodi zinazohitajika kuhifadhiwa bado zinapitiwa.
            Hatua hii haimaanishi kwamba rekodi zote zinazohusiana hufutwa mara moja.
        </div>
        <p class="text-sm text-gray-600">Tazama pia <a class="text-primary underline" href="{{ route('privacy-policy') }}">Sera ya Faragha</a>.</p>
    </div>
</section>
@endsection
