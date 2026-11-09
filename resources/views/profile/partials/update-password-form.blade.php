<section>
    <header>
        <h2 class="text-lg font-bold text-navy">
            Usalama wa Akaunti
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Badilisha nenosiri lako ili kulinda taarifa za akaunti yako.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="'Nenosiri la Sasa'" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-primary focus:ring-primary" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="'Nenosiri Jipya'" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-primary focus:ring-primary" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="'Thibitisha Nenosiri Jipya'" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-primary focus:ring-primary" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button class="!rounded-xl !bg-primary !px-5 !py-3 !text-sm !normal-case !tracking-normal hover:!bg-primaryDark focus:!ring-primary">Badilisha Nenosiri</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Nenosiri limebadilishwa.</p>
            @endif
        </div>
    </form>
</section>
