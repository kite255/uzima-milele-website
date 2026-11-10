{{-- Only shown in Median app mode; regular website navigation is unchanged. --}}
<header class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
    <div class="mx-auto flex h-14 max-w-3xl items-center justify-between gap-3 px-4">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2" aria-label="Uzima Milele - Nyumbani">
            <img src="{{ asset('logo.png') }}" alt="" class="h-9 w-auto max-w-12 object-contain">
            <span class="truncate font-black text-navy">Uzima Milele</span>
        </a>
    </div>
</header>

<nav aria-label="Urambazaji wa programu"
    class="median-app-bottom-nav fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white shadow-lg"
    style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    @php
        $medianTabs = [
            ['route' => 'home', 'label' => 'Nyumbani', 'icon' => 'home', 'active' => request()->routeIs('home')],
            ['route' => 'lessons.index', 'label' => 'Masomo', 'icon' => 'book', 'active' => request()->routeIs('lessons.*')],
            ['route' => 'devotions.index', 'label' => 'Tafakari', 'icon' => 'sun', 'active' => request()->routeIs('devotions.*')],
            ['route' => auth()->check() ? 'dashboard' : 'login', 'label' => 'Yangu', 'icon' => 'learning', 'active' => request()->routeIs('student.*', 'dashboard')],
            ['route' => 'app.account', 'label' => 'Akaunti', 'icon' => 'user', 'active' => request()->routeIs('app.account', 'profile.*', 'register', 'login', 'password.*')],
        ];
    @endphp

    <div class="mx-auto grid h-[68px] max-w-3xl grid-cols-5">
        @foreach ($medianTabs as $tab)
            <a href="{{ route($tab['route']) }}"
                @if($tab['active']) aria-current="page" @endif
                class="flex min-w-0 min-h-[48px] flex-col items-center justify-center gap-1 px-1 text-center transition-colors {{ $tab['active'] ? 'text-primary' : 'text-gray-600 hover:text-primary' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    @switch($tab['icon'])
                        @case('home')
                            <path d="m3 10 9-7 9 7"></path>
                            <path d="M5 9v12h14V9"></path>
                            <path d="M9 21v-7h6v7"></path>
                            @break
                        @case('book')
                            <path d="M12 7v14"></path>
                            <path d="M3 18V5a2 2 0 0 1 2-2h3a4 4 0 0 1 4 4 4 4 0 0 1 4-4h3a2 2 0 0 1 2 2v13h-5a4 4 0 0 0-4 3 4 4 0 0 0-4-3H3Z"></path>
                            @break
                        @case('sun')
                            <circle cx="12" cy="12" r="4"></circle>
                            <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                            @break
                        @case('learning')
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="m8 12 3 3 5-6"></path>
                            @break
                        @case('user')
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21a8 8 0 0 1 16 0"></path>
                            @break
                    @endswitch
                </svg>
                <span class="w-full truncate text-[11px] leading-4 font-semibold">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
