@props(['error' => null])

<input {{ $attributes->merge(['class' => 'input' . ($error ? ' input-error' : '')]) }}>
