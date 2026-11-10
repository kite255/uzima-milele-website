{{-- Displayed only when the Median launch URL has activated app mode. --}}
<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="flex h-14 items-center justify-between gap-3 px-4 mx-auto max-w-3xl">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2" aria-label="Uzima Milele - Nyumbani">
            <img src="{{ asset('logo.png') }}" alt="" class="h-9 w-auto max-w-12 object-contain">
            <span class="truncate font-black text-navy">Uzima Milele</span>
        </a>
        @auth
            <a href="{{ route('profile.edit') }}" class="rounded-full bg-gray-100 px-3 py-2 text-sm font-bold text-navy" aria-label="Wasifu wangu">Wasifu</a>
        @else
            <a href="{{ route('login') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-bold text-white">Ingia</a>
        @endauth
    </div>
</header>

<nav aria-label="Urambazaji wa programu" class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white shadow-lg" style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    <div class="mx-auto grid h-16 max-w-3xl grid-cols-5">
        @php
            $medianTabs = [
                ['route' => 'home', 'label' => 'Nyumbani', 'symbol' => '⌂', 'active' => request()->routeIs('home')],
                ['route' => 'lessons.index', 'label' => 'Masomo', 'symbol' => '▤', 'active' => request()->routeIs('lessons.*')],
                ['route' => 'devotions.index', 'label' => 'Tafakari', 'symbol' => '✧', 'active' => request()->routeIs('devotions.*')],
                ['route' => auth()->check() ? 'student.dashboard' : 'login', 'label' => 'Masomo Yangu', 'symbol' => '✓', 'active' => request()->routeIs('student.*')],
                ['route' => auth()->check() ? 'profile.edit' : 'register', 'label' => 'Akaunti', 'symbol' => '○', 'active' => request()->routeIs('profile.*')],
            ];
        @endphp
        @foreach ($medianTabs as $tab)
            <a href="{{ route($tab['route']) }}"
               @if($tab['active']) aria-current="page" @endif
               class="flex min-w-0 flex-col items-center justify-center gap-0.5 px-1 text-center {{ $tab['active'] ? 'text-primary' : 'text-gray-500' }}">
                <span class="text-xl leading-5" aria-hidden="true">{{ $tab['symbol'] }}</span>
                <span class="truncate w-full text-[10px] font-bold">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
