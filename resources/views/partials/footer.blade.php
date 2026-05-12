<footer class="bg-navy text-white mt-20">

    {{-- SIMPLE SUBSCRIBE SECTION --}}
    <div class="border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                <div>
                    <h2 class="text-2xl font-black">
                        Pokea Tafakari Mpya
                    </h2>

                    <p class="mt-2 text-sm text-white/70">
                        Jiandikishe kupokea tafakari na taarifa kutoka Uzima Milele.
                    </p>
                </div>
<form action="https://uzimamilele.us21.list-manage.com/subscribe/post?u=9484280256702e240d8f3f769&id=01a193b153&f_id=007760e1f0"
      method="POST"
      target="_blank"
      class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">

    <input type="text"
           name="FNAME"
           placeholder="Jina lako"
           class="w-full md:w-64 rounded-full border border-white/20 bg-white px-5 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-accent focus:ring-accent">

    <input type="email"
           name="EMAIL"
           placeholder="Barua pepe yako"
           class="w-full md:w-80 rounded-full border border-white/20 bg-white px-5 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-accent focus:ring-accent"
           required>

    <div aria-hidden="true" style="position: absolute; left: -5000px;">
        <input type="text"
               name="b_9484280256702e240d8f3f769_01a193b153"
               tabindex="-1"
               value="">
    </div>

    <button type="submit"
            class="rounded-full bg-primary px-6 py-3 text-sm font-black text-white hover:bg-primaryDark transition">
        Jiandikishe
    </button>
</form>

            </div>
        </div>
    </div>


    {{-- MAIN FOOTER --}}
    <div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">

        {{-- ABOUT --}}
        <div>
            <h3 class="font-bold text-lg mb-4">
                Uzima Milele
            </h3>

            <p class="text-sm text-white/75 leading-relaxed">
                Huduma ya Kikristo inayotoa elimu ya Biblia, afya na jamii kwa lugha ya Kiswahili.
            </p>
        </div>


        {{-- LINKS --}}
        <div>
            <h3 class="font-bold text-lg mb-4">
                Kurasa
            </h3>

            <ul class="space-y-2 text-sm text-white/75">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-white">
                        Nyumbani
                    </a>
                </li>

                <li>
                    <a href="{{ route('about') }}" class="hover:text-white">
                        Kuhusu sisi
                    </a>
                </li>

                <li>
                    <a href="{{ route('devotions.index') }}" class="hover:text-white">
                        Tafakari
                    </a>
                </li>

                <li>
                    <a href="{{ route('lessons.index') }}" class="hover:text-white">
                        Masomo
                    </a>
                </li>

                <li>
                    <a href="{{ route('children.index') }}" class="hover:text-white">
                        Watoto
                    </a>
                </li>
            </ul>
        </div>


        {{-- SERVICES --}}
        <div>
            <h3 class="font-bold text-lg mb-4">
                Huduma
            </h3>

            <ul class="space-y-2 text-sm text-white/75">
                <li>
                    <a href="{{ route('prayers.testimonies') }}" class="hover:text-white">
                        Maombi & Ushuhuda
                    </a>
                </li>

                <li>
                    <a href="{{ route('contact') }}" class="hover:text-white">
                        Mawasiliano
                    </a>
                </li>

                <li>
                   <a href="{{ route('changia') }}" class="hover:text-white">
    Changia
</a>
                </li>
            </ul>
        </div>


        {{-- CONTACT --}}
        <div>
            <h3 class="font-bold text-lg mb-4">
                Mawasiliano
            </h3>

            <div class="space-y-2 text-sm text-white/75">
                <p>Dar es Salaam, Tanzania</p>

                <p>
                    <a href="mailto:info@uzimamilele.or.tz" class="hover:text-white">
                        info@uzimamilele.or.tz
                    </a>
                </p>

                <p>
                    <a href="mailto:maombi@uzimamilele.or.tz" class="hover:text-white">
                        maombi@uzimamilele.or.tz
                    </a>
                </p>

               <p>+255 764 504 284</p>
            </div>
        </div>

    </div>


    {{-- COPYRIGHT --}}
    <div class="border-t border-white/10 py-4 text-center text-sm text-white/50">
        © {{ date('Y') }} Uzima Milele. All rights reserved.
        <span class="mx-2">•</span>
        Version 3.2.0
    </div>

</footer>