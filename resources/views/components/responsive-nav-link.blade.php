@props(['active'])

@php
$classes = ($active ?? false)
            ? 'responsive-nav-link is-active'
            : 'responsive-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
