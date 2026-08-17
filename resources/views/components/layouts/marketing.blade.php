<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ config('app.available_locales.'.app()->getLocale().'.dir', 'ltr') }}"
>
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
            <a href="{{ url('/') }}#features" class="link">{{ __('marketing.features') }}</a>
            <a href="{{ route('docs') }}" class="link">{{ __('marketing.docs') }}</a>
            <a href="{{ route('blog') }}" class="link">{{ __('marketing.blog') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">{{ __('marketing.dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">{{ __('marketing.sign_in') }}</a>
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('marketing.get_started') }}</a>
            @endauth
            @include('partials.locale-switcher-static')
        </div>

        <main>
            {{ $slot }}
        </main>

        <footer>
            <div class="container footer-top">
                <div class="ms-mw-280">
                    <div class="brand ms-mb-10">
                        <span class="brand-mark">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                        <span class="brand-name">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-muted ms-fs-13">A Laravel Livewire starter for admin-heavy products.</p>
                </div>
                <div class="ms-row-56">
                    <div>
                        <div class="footer-heading">Product</div>
                        <div class="footer-links">
                            <a href="{{ url('/') }}#features" class="link ms-color-text">{{ __('marketing.features') }}</a>
                            <a href="{{ route('docs') }}" class="link ms-color-text">{{ __('marketing.docs') }}</a>
                            <a href="#" class="link ms-color-text">{{ __('marketing.changelog') }}</a>
                        </div>
                    </div>
                    <div>
                        <div class="footer-heading">Company</div>
                        <div class="footer-links">
                            <a href="{{ route('blog') }}" class="link ms-color-text">{{ __('marketing.blog') }}</a>
                            <a href="#" class="link ms-color-text">{{ __('marketing.github') }}</a>
                            <a href="#" class="link ms-color-text">{{ __('marketing.license') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container footer-bottom">
                <div class="text-muted ms-fs-12">© {{ date('Y') }} {{ config('app.name') }}.</div>
                <div class="text-muted ms-fs-12">{{ __('marketing.in_memory') }}</div>
            </div>
        </footer>
    </body>
</html>
