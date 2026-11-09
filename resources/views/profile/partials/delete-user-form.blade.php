<section class="grid gap-6 md:grid-cols-12 md:gap-10">
    <header class="md:col-span-4">\n        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600 ring-1 ring-red-100">\n            <svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"></path></svg>\n        </div>
        <h2 class="mt-4 text-xl font-extrabold text-red-700">
            Futa Akaunti
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Kufuta akaunti yako kunaweza kufuta usajili wa masomo, maendeleo ya kujifunza, majaribio na vyeti vinavyohusishwa na akaunti. Hatua hii haiwezi kutenguliwa. Baadhi ya rekodi zinaweza kuhifadhiwa kulingana na utaratibu wa huduma.
        </p>
    </header>\n    <div class="min-w-0 space-y-4 md:col-span-8">

    @if(auth()->user()?->google_id)
        <div class="rounded-2xl border-l-4 border-accent bg-blue-50 p-4 text-sm text-navy space-y-2">
            <p class="font-bold">Unatumia akaunti ya Google?</p>
            <p>Kwa usalama, mfumo unahitaji nenosiri kuthibitisha kufuta akaunti. Ikiwa hujawahi kuweka nenosiri, ondoka kwenye akaunti kisha tumia "Umesahau nenosiri?" kwenye ukurasa wa kuingia ili kutengeneza nenosiri kupitia barua pepe yako.</p>
            <p>Ikiwa huwezi kutumia njia hiyo, angalia <a href="{{ route('account-deletion') }}" class="font-bold text-primary underline">maelekezo ya kuomba kufuta akaunti</a>.</p>
        </div>
    @endif

    <p class="text-sm text-gray-600">Soma pia <a href="{{ route('account-deletion') }}" class="font-semibold text-primary underline underline-offset-2">utaratibu wa kufuta akaunti na taarifa zinazoweza kuhifadhiwa</a>.</p>

    <x-danger-button type="button" class="!rounded-xl !px-5 !py-3 !text-sm !normal-case !tracking-normal"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Futa Akaunti</x-danger-button>

    </div>\n\n    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-red-700">
                Una uhakika unataka kufuta akaunti yako?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Una uhakika unataka kufuta akaunti yako? Hatua hii inaweza kufuta maendeleo ya masomo, majaribio na vyeti. Weka nenosiri lako kuthibitisha.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Nenosiri" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:border-red-500 focus:ring-red-500"
                    placeholder="Nenosiri"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    Ghairi
                </x-secondary-button>

                <x-danger-button class="!rounded-xl !px-5 !py-3 !text-sm !normal-case !tracking-normal">
                    Futa Akaunti
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
