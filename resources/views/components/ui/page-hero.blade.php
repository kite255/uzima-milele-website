@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge([
    'class' => 'relative overflow-hidden rounded-3xl bg-gradient-to-r from-navy via-primaryDark to-primary p-6 md:p-8 text-white shadow-lg'
]) }}>
    <div class="relative z-10">
        @if($eyebrow)
            <p class="text-sm font-bold text-white/80">
                {{ $eyebrow }}
            </p>
        @endif

        <h1 class="mt-2 text-3xl md:text-4xl font-black">
            {{ $title }}
        </h1>

        @if($description)
            <p class="mt-3 max-w-3xl text-sm md:text-base text-white/85">
                {{ $description }}
            </p>
        @endif

        @if(trim((string) $slot))
            <div class="mt-5">
                {{ $slot }}
            </div>
        @endif
    </div>

    <div class="absolute -right-12 -bottom-14 h-56 w-56 rounded-full bg-white/10"></div>
    <div class="absolute right-24 top-6 h-24 w-24 rounded-full bg-white/10"></div>
</div>
