@props([
    'label',
    'tone' => 'gray',
])

@php
    $classes = match ($tone) {
        'green' => 'bg-green-100 text-green-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'red' => 'bg-red-100 text-red-700',
        'blue' => 'bg-primary/10 text-primary',
        'navy' => 'bg-navy/10 text-navy',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex rounded-full px-3 py-1 text-xs font-bold ' . $classes
]) }}>
    {{ $label }}
</span>
