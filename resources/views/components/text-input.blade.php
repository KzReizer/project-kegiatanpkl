@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'text-input']) }}>
