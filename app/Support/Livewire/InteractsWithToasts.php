<?php

namespace App\Support\Livewire;

/**
 * Small helper for Livewire/Volt components to fire an immediate, client-side
 * toast for the acting user. The `notify` browser event is picked up by the
 * global <x-toast-hub /> vanilla-JS listener (resources/js/app.js) rendered in
 * the app layout.
 */
trait InteractsWithToasts
{
    public function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', type: $type, message: $message);
    }
}
