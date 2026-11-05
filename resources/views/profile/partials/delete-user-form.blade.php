<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
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

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Una uhakika unataka kufuta akaunti yako? Hatua hii inaweza kufuta maendeleo ya masomo, majaribio na vyeti. Weka nenosiri lako kuthibitisha.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
