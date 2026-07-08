<button {{ $attributes->merge(['type' => 'button', 'class' => 'secondary-button']) }}>
    {{ $slot }}
</button>
