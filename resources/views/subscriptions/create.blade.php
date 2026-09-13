@extends('layouts.app')

@section('content')
    <section class="bg-gray-50 py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4">

            <div class="mb-10 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-primary">
                    Uzima Milele
                </p>

                <h1 class="mt-3 text-3xl font-black text-navy sm:text-4xl">
                    Jiandikishe Kupokea Tafakari
                </h1>

                <p class="mt-4 leading-relaxed text-gray-600">
                    Pokea tafakari, masomo na taarifa muhimu kutoka Uzima Milele moja kwa moja kwenye barua pepe yako.
                </p>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

                @if (session('subscription_success'))
                    <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                        {{ session('subscription_success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                        <p class="mb-2 text-sm font-bold text-red-800">
                            Tafadhali sahihisha taarifa zifuatazo:
                        </p>

                        <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('email-subscribers.store') }}"
                    method="POST"
                    class="space-y-6"
                >
                    @csrf

                    <div>
                        <label
                            for="name"
                            class="mb-2 block text-sm font-bold text-gray-800"
                        >
                            Jina Kamili
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            required
                            placeholder="Mfano: John Doe"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >
                    </div>

                    <div>
                        <label
                            for="email"
                            class="mb-2 block text-sm font-bold text-gray-800"
                        >
                            Barua Pepe
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            placeholder="Mfano: jina@example.com"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >
                    </div>

                    <div>
                        <label
                            for="phone"
                            class="mb-2 block text-sm font-bold text-gray-800"
                        >
                            Namba ya Simu

                            <span class="font-normal text-gray-500">
                                (Si lazima)
                            </span>
                        </label>

                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            placeholder="Mfano: +255 7XX XXX XXX"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                        <label
                            for="consent"
                            class="flex cursor-pointer items-start gap-3"
                        >
                            <input
                                id="consent"
                                type="checkbox"
                                name="consent"
                                value="1"
                                @checked(old('consent'))
                                required
                                class="mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                            >

                            <span class="text-sm leading-relaxed text-gray-700">
                                Ninakubali kupokea tafakari, masomo na taarifa kutoka Uzima Milele kwa barua pepe.
                                Ninaelewa kuwa ninaweza kubadilisha mapendeleo yangu au kujiondoa wakati wowote kupitia kiungo kilicho kwenye barua pepe.
                            </span>
                        </label>

                        @error('consent')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                   <button
    type="submit"
    class="flex min-h-[52px] w-full items-center justify-center rounded-2xl bg-primary px-6 py-3 text-base font-black leading-none text-white transition hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
>
    Jiandikishe
</button>
                </form>

            </div>
        </div>
    </section>
@endsection