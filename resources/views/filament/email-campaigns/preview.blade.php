<div class="space-y-6">

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">

        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
            Kichwa cha Barua Pepe
        </p>

        <h2 class="mt-2 text-xl font-bold text-gray-900">
            {{ $campaign->subject }}
        </h2>

    </div>


    @if ($campaign->isDevotion() && $campaign->devotion)

        <div class="space-y-5">

            <div>
                <p class="text-sm font-bold text-gray-500">
                    Tafakari
                </p>

                <h3 class="mt-1 text-2xl font-black text-gray-900">
                    {{ $campaign->devotion->title }}
                </h3>
            </div>


            @if (filled($campaign->devotion->feature_text))

                <div class="rounded-xl bg-gray-50 p-5">
                    {!! $campaign->devotion->feature_text !!}
                </div>

            @endif


            @if (filled($campaign->devotion->scripture_reference))

                <div
                    class="rounded-xl bg-gray-900 p-5 text-white"
                >
                    <p class="font-bold">
                        {{ $campaign->devotion->scripture_reference }}
                    </p>

                    @if (filled($campaign->devotion->scripture_text))
                        <div class="mt-3 leading-relaxed">
                            {!! $campaign->devotion->scripture_text !!}
                        </div>
                    @endif
                </div>

            @endif


            @if (filled($campaign->devotion->lesson))

                <div>
                    <h4 class="font-bold text-gray-900">
                        Funzo
                    </h4>

                    <div class="mt-2 leading-relaxed text-gray-700">
                        {!! $campaign->devotion->lesson !!}
                    </div>
                </div>

            @endif

        </div>

    @elseif ($campaign->isCustom())

        <div
            class="prose max-w-none rounded-xl border border-gray-200 bg-white p-6"
        >
            {!! $campaign->content !!}
        </div>

    @else

        <div
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800"
        >
            Maudhui ya kampeni hayajapatikana.
        </div>

    @endif

</div>
