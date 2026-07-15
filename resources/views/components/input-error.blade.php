@props(['message'])

@if ($message)
    <p {{ $attributes->merge(['class' => 'ds-error']) }}>
        {{ $message }}
    </p>
@endif
