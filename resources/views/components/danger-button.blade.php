<button {{ $attributes->merge(['type' => 'submit', 'class' => 'danger-button']) }}>
    {{ $slot }}
</button>
