@extends('layouts.app')

@section('content')

    <section class="bg-gray-50 py-16 sm:py-20">

        <div class="max-w-2xl mx-auto px-4">

            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm sm:p-10"
            >

                {{-- STATUS ICON --}}
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-50"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-9 w-9 text-green-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>


                {{-- TITLE --}}
                <h1
                    class="mt-6 text-2xl font-black leading-tight text-navy sm:text-4xl"
                >
                    Umejiondoa Kwenye Orodha ya Barua Pepe
                </h1>


                {{-- MESSAGE --}}
                @if ($alreadyUnsubscribed)

                    <p
                        class="mx-auto mt-4 max-w-xl text-gray-600 leading-relaxed"
                    >
                        Tayari ulikuwa umejiondoa kwenye orodha ya barua pepe
                        ya Uzima Milele.
                    </p>

                @else

                    <p
                        class="mx-auto mt-4 max-w-xl text-gray-600 leading-relaxed"
                    >
                        Ombi lako limepokelewa kwa mafanikio.
                        Hutapokea tena tafakari na taarifa kutoka Uzima Milele
                        kupitia barua pepe hii.
                    </p>

                @endif


                {{-- EMAIL --}}
                @if (filled($subscriber->email))

                    <div
                        class="mt-7 rounded-2xl border border-gray-100 bg-gray-50 px-5 py-5"
                    >

                        <p
                            class="text-xs font-bold uppercase tracking-wider text-gray-500"
                        >
                            Barua Pepe
                        </p>

                        <p
                            class="mt-1 break-words text-sm font-bold text-gray-800"
                        >
                            {{ $subscriber->email }}
                        </p>

                    </div>

                @endif


                {{-- ACTIONS --}}
                <div class="mt-8 space-y-3">

                    {{-- PRIMARY ACTION --}}
                    <a
                        href="{{ route('subscriptions.create') }}"
                        class="block w-full rounded-full bg-primary px-6 py-4 text-sm font-black text-white transition hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        Jiandikishe Tena
                    </a>


                    {{-- SECONDARY ACTION --}}
                    <a
                        href="{{ route('email-subscribers.preferences', $subscriber->unsubscribe_token) }}"
                        class="block w-full rounded-full border border-gray-300 bg-white px-6 py-4 text-sm font-bold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
                    >
                        Badilisha Mapendeleo
                    </a>

                </div>


                {{-- BACK HOME --}}
                <div
                    class="mt-8 border-t border-gray-100 pt-6"
                >

                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
                    >
                        Rudi Nyumbani
                    </a>

                </div>

            </div>

        </div>

    </section>

@endsection