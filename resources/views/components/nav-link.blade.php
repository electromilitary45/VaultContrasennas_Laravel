@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link text-dark active'
            : 'nav-link text-dark';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
