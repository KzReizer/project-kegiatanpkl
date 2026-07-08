@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'session-status']) }}>
        {{ $status }}
    </div>
@endif
