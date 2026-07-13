@props(['message'])

@if ($message)
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-sm text-red-600 dark:text-red-400']) }}>
        {{ $message }}
    </p>
@endif
