<x-filament-panels::page>

    <div class="space-y-6">

        {{-- ============================================================
            PUBLIC SUBSCRIPTION PAGE
        ============================================================ --}}
        <x-filament::section>
            <x-slot name="heading">
                Ukurasa wa Usajili
            </x-slot>

            <x-slot name="description">
                Tumia kiungo hiki kuwaelekeza watu kwenye ukurasa wa kujiandikisha kupokea tafakari na taarifa kwa barua pepe.
            </x-slot>

            @php
                $subscriptionUrl = route('subscriptions.create');
            @endphp

            <div
                x-data="{
                    copied: false,

                    async copySubscriptionLink() {
                        try {
                            await navigator.clipboard.writeText(
                                @js($subscriptionUrl)
                            );

                            this.copied = true;

                            setTimeout(() => {
                                this.copied = false;
                            }, 2000);
                        } catch (error) {
                            console.error(
                                'Imeshindikana kunakili kiungo.',
                                error
                            );
                        }
                    }
                }"
                class="space-y-4"
            >

                {{-- URL DISPLAY --}}
                <div>
                    <label
                        for="subscription_page_url"
                        class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                    >
                        Kiungo cha Ukurasa
                    </label>

                    <div
                        class="flex flex-col gap-3 sm:flex-row"
                    >
                        <input
                            id="subscription_page_url"
                            type="text"
                            value="{{ $subscriptionUrl }}"
                            readonly
                            class="block w-full rounded-lg border-gray-300 bg-gray-50 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                        >

                        <div
                            class="flex flex-col gap-2 sm:flex-row"
                        >

                            {{-- OPEN PAGE --}}
                            <a
                                href="{{ $subscriptionUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="fi-btn fi-btn-size-md relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm outline-none transition duration-75 hover:bg-primary-500 focus-visible:ring-2 focus-visible:ring-primary-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5M10.5 13.5 21 3m0 0h-6.75M21 3v6.75"
                                    />
                                </svg>

                                <span>
                                    Fungua Ukurasa
                                </span>
                            </a>


                            {{-- COPY LINK --}}
                            <button
                                type="button"
                                x-on:click="copySubscriptionLink()"
                                class="fi-btn fi-btn-size-md relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm outline-none transition duration-75 hover:bg-gray-500 focus-visible:ring-2 focus-visible:ring-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75A1.125 1.125 0 0 1 3.75 20.625v-9.75c0-.621.504-1.125 1.125-1.125H8.25m7.5 7.5h3.375c.621 0 1.125-.504 1.125-1.125v-9.75c0-.621-.504-1.125-1.125-1.125h-9.75A1.125 1.125 0 0 0 8.25 6.375V9.75m7.5 7.5h-6.375A1.125 1.125 0 0 1 8.25 16.125V9.75"
                                    />
                                </svg>

                                <span
                                    x-show="! copied"
                                >
                                    Nakili Kiungo
                                </span>

                                <span
                                    x-show="copied"
                                    x-cloak
                                >
                                    Kimenakiliwa
                                </span>
                            </button>

                        </div>
                    </div>

                    <p
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        Hiki ndicho kiungo cha umma ambacho unaweza kunakili na kushiriki kwenye WhatsApp, SMS, mitandao ya kijamii au tovuti nyingine.
                    </p>
                </div>

            </div>
        </x-filament::section>


        {{-- ============================================================
            EMAIL SETTINGS FORM
        ============================================================ --}}
        <form
            wire:submit="save"
            class="space-y-6"
        >

            {{ $this->form }}

            <div class="flex justify-end">

                <x-filament::button
                    type="submit"
                    icon="heroicon-o-check"
                >
                    Hifadhi Mipangilio
                </x-filament::button>

            </div>

        </form>

    </div>

</x-filament-panels::page>