<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name').' — Laravel Livewire admin starter' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=barlow:400,500,700|barlow-condensed:400,600" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="antialiased">
        <div class="nav">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                <span class="brand-name">{{ config('app.name') }}</span>
            </a>
            <a href="{{ url('/') }}#features" class="link">Features</a>
            <a href="{{ route('docs') }}" class="link">Docs</a>
            <a href="{{ route('blog') }}" class="link">Blog</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            @endauth
        </div>

        <main>
            {{ $slot }}
        </main>

        <footer>
            <div class="container footer-top">
                <div style="max-width: 280px;">
                    <div class="brand" style="margin-bottom: 10px;">
                        <span class="brand-mark">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                        <span class="brand-name">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-muted" style="font-size: 13px;">A Laravel Livewire starter for admin-heavy products.</p>
                </div>
                <div style="display: flex; gap: 56px; flex-wrap: wrap;">
                    <div>
                        <div class="footer-heading">Product</div>
                        <div class="footer-links">
                            <a href="{{ url('/') }}#features" class="link" style="color: var(--color-text);">Features</a>
                            <a href="{{ route('docs') }}" class="link" style="color: var(--color-text);">Docs</a>
                            <a href="#" class="link" style="color: var(--color-text);">Changelog</a>
                        </div>
                    </div>
                    <div>
                        <div class="footer-heading">Company</div>
                        <div class="footer-links">
                            <a href="{{ route('blog') }}" class="link" style="color: var(--color-text);">Blog</a>
                            <a href="#" class="link" style="color: var(--color-text);">GitHub</a>
                            <a href="#" class="link" style="color: var(--color-text);">License</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container footer-bottom">
                <div class="text-muted" style="font-size: 12px;">© {{ date('Y') }} {{ config('app.name') }}.</div>
                <div class="text-muted" style="font-size: 12px;">In loving memory of Majida — this project carries her name forward.</div>
            </div>
        </footer>

        <style>
            .footer-top { padding: 48px 48px 32px; display: flex; justify-content: space-between; gap: 40px; flex-wrap: wrap; }
            .footer-bottom { border-top: 1px solid var(--color-divider); padding: 20px 48px 40px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
            .footer-heading { color: rgba(var(--ink), 0.6); font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 12px; }
            .footer-links { display: flex; flex-direction: column; gap: 8px; font-size: 13px; }
            @media (max-width: 900px) {
                .footer-top, .footer-bottom { padding-left: 24px; padding-right: 24px; }
            }
        </style>
    </body>
</html>
