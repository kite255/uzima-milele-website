<section class="grid gap-6 md:grid-cols-12 md:gap-10">
    <header class="md:col-span-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-primary ring-1 ring-sky-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="5" y="10" width="14" height="11" rx="2"></rect>
                <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
            </svg>
        </div>
        <h2 class="mt-4 text-xl font-extrabold text-navy">Usalama wa Akaunti</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Tumia nenosiri imara ambalo hutumii kwenye akaunti nyingine.</p>
    </header>

    <div class="min-w-0 md:col-span-8">
        <form method="post" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('put')

            <div>
                <x-input-label for="update_password_current_password" :value="'Nenosiri la Sasa'" class="!text-sm !font-semibold !text-slate-700" />
                <x-text-input id="update_password_current_password" name="current_password" type="password"
                    class="mt-2 block w-full !rounded-xl !border-slate-300 !bg-white px-4 py-3 text-base !shadow-none focus:!border-primary focus:!ring-primary"
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="'Nenosiri Jipya'" class="!text-sm !font-semibold !text-slate-700" />
                <x-text-input id="update_password_password" name="password" type="password"
                    class="mt-2 block w-full !rounded-xl !border-slate-300 !bg-white px-4 py-3 text-base !shadow-none focus:!border-primary focus:!ring-primary"
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="'Thibitisha Nenosiri Jipya'" class="!text-sm !font-semibold !text-slate-700" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                    class="mt-2 block w-full !rounded-xl !border-slate-300 !bg-white px-4 py-3 text-base !shadow-none focus:!border-primary focus:!ring-primary"
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
                <x-primary-button class="!rounded-xl !bg-primary !px-6 !py-3 !text-sm !normal-case !tracking-normal hover:!bg-primaryDark focus:!ring-primary">
                    Badilisha Nenosiri
                </x-primary-button>
                @if (session('status') === 'password-updated')
                    <p role="status" aria-live="polite" class="text-sm font-semibold text-green-700">Nenosiri limebadilishwa.</p>
                @endif
            </div>
        </form>
    </div>
</section>
