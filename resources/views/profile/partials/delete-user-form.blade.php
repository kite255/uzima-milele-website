<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-red-700">
            Futa Akaunti
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Kufuta akaunti yako kunaweza kufuta usajili wa masomo, maendeleo ya kujifunza, majaribio na vyeti vinavyohusishwa na akaunti. Hatua hii haiwezi kutenguliwa. Baadhi ya rekodi zinaweza kuhifadhiwa kulingana na utaratibu wa huduma.
        </p>
    </header>

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

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
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
