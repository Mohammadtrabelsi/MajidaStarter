@php
    $flash = session('status');
@endphp

{{-- Global toast container. Toasts are rendered client-side by
     resources/js/app.js in response to the `notify` browser event dispatched
     from Livewire components (see App\Support\Livewire\InteractsWithToasts). --}}
<div
    aria-live="polite"
    aria-atomic="true"
    data-toast-hub
    @if ($flash) data-toast-flash="{{ $flash }}" @endif
    style="position:fixed;top:1rem;inset-inline-end:1rem;z-index:60;display:flex;flex-direction:column;gap:.5rem;max-width:22rem;"
></div>
