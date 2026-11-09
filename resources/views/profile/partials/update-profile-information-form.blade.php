<section>
    <header>
        <h2 class="text-lg font-bold text-navy">
            Taarifa Binafsi
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Sasisha jina na barua pepe zinazotumika kwenye akaunti yako.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="'Jina Kamili'" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-primary focus:ring-primary" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="'Barua Pepe'" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-primary focus:ring-primary" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Barua pepe yako bado haijathibitishwa.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Bonyeza hapa kutumiwa tena ujumbe wa uthibitisho.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Kiungo kipya cha kuthibitisha barua pepe kimetumwa.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button class="!rounded-xl !bg-primary !px-5 !py-3 !text-sm !normal-case !tracking-normal hover:!bg-primaryDark focus:!ring-primary">Hifadhi Mabadiliko</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Mabadiliko yamehifadhiwa.</p>
            @endif
        </div>
    </form>
</section>
