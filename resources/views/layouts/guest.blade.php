<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ config('app.available_locales.'.app()->getLocale().'.dir', 'ltr') }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=barlow:400,500,700|barlow-condensed:400,600" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased">
        <div style="position: absolute; top: 18px; inset-inline-end: 20px; z-index: 10; display: flex; gap: 8px;">
            @include('partials.locale-switcher')
            @include('partials.theme-toggle')
        </div>
        <div class="auth-split">
            <div class="auth-hero hatch">
                <div class="auth-hero-tint"></div>
                <div class="auth-hero-body">
                    <span class="brand-mark" style="background: var(--color-bg); color: var(--color-accent-900); margin-bottom: 18px;">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                    <h2 style="color: var(--color-bg);">{{ __('app.welcome') }} {{ config('app.name') }}.</h2>
                    <p style="opacity: 0.85; color: var(--color-bg); margin: 0;">{{ __('app.auth_scaffolding') }}</p>
                </div>
            </div>

            <div class="auth-form-side">
                <div style="width: 100%; max-width: 360px;">
                    <a href="{{ url('/') }}" wire:navigate class="brand" style="margin-bottom: 36px;">
                        <span class="brand-mark">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                        <span class="brand-name">{{ config('app.name') }}</span>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- Guest layout styles moved to public/css/guest.css -->
        <link rel="stylesheet" href="{{ asset('css/guest.css') }}">

        @livewireScripts
    </body>
</html>
