@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge([
    'class' => 'overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm'
]) }}>
    @if($title || $description)
        <div class="border-b border-gray-100 p-6">
            @if($title)
                <h2 class="text-2xl font-black text-navy">
                    {{ $title }}
                </h2>
            @endif

            @if($description)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
