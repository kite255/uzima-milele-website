@extends('layouts.app')

@section('content')

    <section class="bg-gray-50 py-16 sm:py-20">

        <div class="max-w-3xl mx-auto px-4">

            <div class="text-center mb-10">

                <p class="text-sm font-bold uppercase tracking-wider text-primary">
                    Uzima Milele
                </p>

                <h1 class="mt-3 text-3xl sm:text-4xl font-black text-navy">
                    Mapendeleo ya Barua Pepe
                </h1>

                <p class="mt-4 text-gray-600 leading-relaxed">
                    Sasisha taarifa zako na uchague kama ungependa kuendelea
                    kupokea tafakari na taarifa kutoka Uzima Milele.
                </p>

            </div>

            <div class="rounded-3xl bg-white shadow-sm border border-gray-100 p-6 sm:p-8">

                @if (session('preferences_success'))

                    <div
                        class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800"
                    >
                        {{ session('preferences_success') }}
                    </div>

                @endif

                @if ($errors->any())

                    <div
                        class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4"
                    >

                        <p class="font-bold text-sm text-red-800 mb-2">
                            Tafadhali sahihisha taarifa zifuatazo:
                        </p>

                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>

                @endif

                <form
                    action="{{ route('email-subscribers.preferences.update', $subscriber->unsubscribe_token) }}"
                    method="POST"
                    class="space-y-6"
                >

                    @csrf
                    @method('PATCH')

                    {{-- FULL NAME --}}
                    <div>

                        <label
                            for="name"
                            class="block text-sm font-bold text-gray-800 mb-2"
                        >
                            Jina Kamili
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $subscriber->name) }}"
                            autocomplete="name"
                            required
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-primary focus:ring-primary"
                        >

                    </div>

                    {{-- EMAIL --}}
                    <div>

                        <label
                            for="email"
                            class="block text-sm font-bold text-gray-800 mb-2"
                        >
                            Barua Pepe
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $subscriber->email) }}"
                            autocomplete="email"
                            required
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-primary focus:ring-primary"
                        >

                    </div>

                    {{-- PHONE --}}
                    <div>

                        <label
                            for="phone"
                            class="block text-sm font-bold text-gray-800 mb-2"
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
                            value="{{ old('phone', $subscriber->phone) }}"
                            autocomplete="tel"
                            placeholder="+255 7XX XXX XXX"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >

                    </div>

                    {{-- EMAIL SUBSCRIPTION STATUS --}}
                    <div
                        class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-5"
                    >

                        <label
                            for="receive_emails"
                            class="flex items-start gap-3 cursor-pointer"
                        >

                            <input
                                id="receive_emails"
                                type="checkbox"
                                name="receive_emails"
                                value="1"
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                @checked(
                                    old(
                                        'receive_emails',
                                        $subscriber->status === 'subscribed'
                                    )
                                )
                            >

                            <span>

                                <span class="block font-bold text-gray-900">
                                    Endelea kupokea tafakari na taarifa kwa barua pepe
                                </span>

                                <span class="mt-1 block text-sm text-gray-600 leading-relaxed">
                                    Ondoa alama ikiwa hutaki kuendelea kupokea
                                    tafakari na taarifa kutoka Uzima Milele kwa barua pepe.
                                </span>

                            </span>

                        </label>

                    </div>

                    {{-- SAVE --}}
                    <div class="pt-4">

                        <button
                            type="submit"
                            class="w-full rounded-full bg-primary px-6 py-4 text-sm font-black text-white hover:bg-primaryDark transition"
                        >
                            Hifadhi Mapendeleo
                        </button>

                    </div>

                </form>

                {{-- BACK HOME --}}
                <div class="mt-8 border-t border-gray-100 pt-6 text-center">

                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Rudi Nyumbani
                    </a>

                </div>

            </div>

        </div>

    </section>

@endsection