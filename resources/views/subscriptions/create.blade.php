@extends('layouts.app')

@section('content')
    <section class="bg-gray-50 py-16 sm:py-20">
        <div class="max-w-3xl mx-auto px-4">

            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-wider text-primary">
                    Uzima Milele
                </p>

                <h1 class="mt-3 text-3xl sm:text-4xl font-black text-navy">
                    Jiandikishe Kupokea Tafakari
                </h1>

                <p class="mt-4 text-gray-600 leading-relaxed">
                    Pokea tafakari, masomo na taarifa muhimu kutoka Uzima Milele moja kwa moja kwenye barua pepe yako.
                </p>
            </div>

            <div class="rounded-3xl bg-white shadow-sm border border-gray-100 p-6 sm:p-8">

                @if (session('subscription_success'))
                    <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                        {{ session('subscription_success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
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
                    action="{{ route('email-subscribers.store') }}"
                    method="POST"
                    class="space-y-6"
                >
                    @csrf

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
                            value="{{ old('name') }}"
                            autocomplete="name"
                            required
                            placeholder="Mfano: Kitenken Lucas Ryoba"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >
                    </div>

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
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            placeholder="Mfano: +255 7XX XXX XXX"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary"
                        >
                    </div>

                    <div class="rounded-2xl bg-gray-50 px-4 py-4 text-sm text-gray-600 leading-relaxed">
                        Kwa kujiandikisha, utakubali kupokea tafakari na taarifa kutoka Uzima Milele.
                        Unaweza kujiondoa wakati wowote kupitia kiungo kilicho kwenye barua pepe.
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-primary px-6 py-3.5 text-sm font-black text-white hover:bg-primaryDark transition"
                    >
                        Jiandikishe
                    </button>
                </form>

            </div>
        </div>
    </section>
@endsection