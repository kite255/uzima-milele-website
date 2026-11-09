<section class="grid gap-6 md:grid-cols-12 md:gap-10">
    <header class="md:col-span-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-primary ring-1 ring-sky-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="8" r="4"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0"></path>
            </svg>
        </div>
        <h2 class="mt-4 text-xl font-extrabold text-navy">Taarifa Binafsi</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Sasisha jina lako na barua pepe inayotumika katika akaunti.</p>
    </header>

    <div class="min-w-0 md:col-span-8">
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="name" :value="'Jina Kamili'" class="!text-sm !font-semibold !text-slate-700" />
                <x-text-input id="name" name="name" type="text"
                    class="mt-2 block w-full !rounded-xl !border-slate-300 !bg-white px-4 py-3 text-base !shadow-none focus:!border-primary focus:!ring-primary"
                    :value="old('name', $user->name)" required autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="'Barua Pepe'" class="!text-sm !font-semibold !text-slate-700" />
                <x-text-input id="email" name="email" type="email"
                    class="mt-2 block w-full !rounded-xl !border-slate-300 !bg-white px-4 py-3 text-base !shadow-none focus:!border-primary focus:!ring-primary"
                    :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">
                        <p>Barua pepe yako bado haijathibitishwa.</p>
                        <button type="submit" form="send-verification" class="mt-2 font-semibold text-primary underline underline-offset-2">
                            Tuma tena ujumbe wa uthibitisho
                        </button>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-green-700">Kiungo kipya cha uthibitisho kimetumwa.</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
                <x-primary-button class="!rounded-xl !bg-primary !px-6 !py-3 !text-sm !normal-case !tracking-normal hover:!bg-primaryDark focus:!ring-primary">
                    Hifadhi Mabadiliko
                </x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p role="status" aria-live="polite" class="inline-flex items-center gap-2 text-sm font-semibold text-green-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"></path></svg>
                        Mabadiliko yamehifadhiwa.
                    </p>
                @endif
            </div>
        </form>
    </div>
</section>
