@props([
    'label',
    'value',
    'accent' => 'primary',
])

@php
    $accentClass = match ($accent) {
        'green' => 'border-green-500 text-green-600',
        'accent' => 'border-accent text-navy',
        'navy' => 'border-navy text-navy',
        'red' => 'border-red-500 text-red-600',
        default => 'border-primary text-primary',
    };
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-gray-100 border-t-4 bg-white p-6 shadow-sm ' . $accentClass
]) }}>
    <p class="text-sm text-gray-500">
        {{ $label }}
    </p>

    <div class="mt-2 text-3xl font-black">
        {{ $value }}
    </div>
</div>
