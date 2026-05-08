<nav class="bg-white shadow sticky top-0 z-50" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="h-10">
            <span class="font-bold text-lg text-navy">Uzima Milele</span>
        </a>

        {{-- DESKTOP MENU --}}
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">

            <a href="{{ route('home') }}"
               class="hover:text-primary {{ request()->routeIs('home') ? 'text-primary font-semibold' : '' }}">
                Nyumbani
            </a>

            <a href="{{ route('about') }}"
               class="hover:text-primary {{ request()->routeIs('about') ? 'text-primary font-semibold' : '' }}">
                Kuhusu sisi
            </a>

            <a href="{{ route('devotions.index') }}"
               class="hover:text-primary {{ request()->routeIs('devotions.*') ? 'text-primary font-semibold' : '' }}">
                Tafakari
            </a>

           {{--
            <a href="/books"
               class="hover:text-primary {{ request()->is('books*') ? 'text-primary font-semibold' : '' }}">
                Vitabu vya mwezi
            </a>
           
           --}} 
           
           
           
          

            <a href="{{ route('children.index') }}"
               class="hover:text-primary {{ request()->routeIs('children.*') ? 'text-primary font-semibold' : '' }}">
                Watoto
            </a>

            <a href="{{ route('prayers.testimonies') }}"
               class="hover:text-primary {{ request()->routeIs('prayers.testimonies') ? 'text-primary font-semibold' : '' }}">
                Maombi & ushuhuda
            </a>

            <a href="{{ route('lessons.index') }}"
               class="hover:text-primary {{ request()->routeIs('lessons.*') ? 'text-primary font-semibold' : '' }}">
                Jifunze Biblia
            </a>

            {{-- NOTIFICATIONS --}}
            @auth
                @if(Route::has('notifications.index'))
                    <a href="{{ route('notifications.index') }}"
                       title="Notifications"
                       class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border transition
                              {{ request()->is('notifications*')
                                    ? 'bg-primary text-white border-primary shadow-md'
                                    : 'bg-primary/10 text-primary border-primary/20 hover:bg-primary hover:text-white hover:border-primary hover:shadow-md' }}">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                        </svg>

                        @if(($unreadNotificationsCount ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-[10px] font-black text-navy ring-2 ring-white">
                                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                            </span>
                        @endif
                    </a>
                @endif
            @endauth

        </div>

        {{-- DESKTOP CTA / AUTH --}}
        <div class="hidden md:flex items-center gap-3">

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="/admin"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Admin
                    </a>
                @elseif(auth()->user()->role === 'instructor' && Route::has('instructor.dashboard'))
                    <a href="{{ route('instructor.dashboard') }}"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('student.dashboard') }}"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Dashboard
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-full font-semibold hover:bg-red-700 transition">
                        Toka
                    </button>
                </form>
            @endauth

            <a href="/changia"
               class="px-5 py-2 bg-primary text-white rounded-full font-semibold hover:bg-primaryDark transition">
                Changia
            </a>
        </div>

        {{-- MOBILE BUTTON --}}
        <button @click="open = !open" class="md:hidden text-navy">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-7 w-7"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path x-show="!open"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"/>

                <path x-show="open"
                      x-cloak
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

    </div>

    {{-- MOBILE MENU --}}
    <div x-show="open" x-transition x-cloak class="md:hidden bg-white border-t px-4 pb-4 space-y-3">

        <a href="{{ route('home') }}"
           class="block py-2 {{ request()->routeIs('home') ? 'text-primary font-semibold' : '' }}">
            Nyumbani
        </a>

        <a href="{{ route('about') }}"
           class="block py-2 {{ request()->routeIs('about') ? 'text-primary font-semibold' : '' }}">
            Kuhusu sisi
        </a>

        <a href="{{ route('devotions.index') }}"
           class="block py-2 {{ request()->routeIs('devotions.*') ? 'text-primary font-semibold' : '' }}">
            Tafakari
        </a>


        {{--
          <a href="/books"
           class="block py-2 {{ request()->is('books*') ? 'text-primary font-semibold' : '' }}">
            Vitabu vya mwezi
        </a>
        --}}
      

        <a href="{{ route('children.index') }}"
           class="block py-2 {{ request()->routeIs('children.*') ? 'text-primary font-semibold' : '' }}">
            Watoto
        </a>

        <a href="{{ route('prayers.testimonies') }}"
           class="block py-2 {{ request()->routeIs('prayers.testimonies') ? 'text-primary font-semibold' : '' }}">
            Maombi & ushuhuda
        </a>

        <a href="{{ route('lessons.index') }}"
           class="block py-2 {{ request()->routeIs('lessons.*') ? 'text-primary font-semibold' : '' }}">
            Jifunze Biblia
        </a>

        @auth
            {{-- MOBILE NOTIFICATIONS --}}
            @if(Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center justify-between rounded-xl px-4 py-3 font-bold text-navy hover:bg-primary/10 hover:text-primary transition">

                    <span class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                            </svg>
                        </span>

                        Notifications
                    </span>

                    @if(($unreadNotificationsCount ?? 0) > 0)
                        <span class="rounded-full bg-accent px-2.5 py-1 text-xs font-black text-navy">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->role === 'admin')
                <a href="/admin" class="block py-2 font-semibold text-navy">
                    Admin
                </a>
            @elseif(auth()->user()->role === 'instructor' && Route::has('instructor.dashboard'))
                <a href="{{ route('instructor.dashboard') }}" class="block py-2 font-semibold text-navy">
                    Dashboard
                </a>
            @else
                <a href="{{ route('student.dashboard') }}" class="block py-2 font-semibold text-navy">
                    Dashboard
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="w-full text-left rounded-xl bg-red-600 px-4 py-3 font-bold text-white hover:bg-red-700 transition">
                    Toka
                </button>
            </form>
        @endauth

        <a href="/changia"
           class="block mt-3 text-center bg-primary text-white py-2 rounded-full font-bold hover:bg-primaryDark transition">
            Changia
        </a>

    </div>
</nav>