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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="antialiased">
        <div class="nav">
            <a href="{{ url('/') }}" class="brand">
                <x-brand-mark />
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

        <footer class="site-footer pt-5 mt-5">
            <div class="container footer-grid">
                <div class="footer-brand">
                    <a href="{{ url('/') }}" class="brand">
                        <x-brand-mark />
                        <span class="brand-name">{{ config('app.name') }}</span>
                    </a>
                    <p class="text-muted footer-brand-copy">{{ __('marketing.tagline') }}</p>
                    <a href="{{ route('register') }}" class="btn btn-primary mb-4">{{ __('marketing.get_started') }}</a>
                </div>
                <nav aria-label="Product">
                    <div class="footer-heading">{{ __('marketing.product') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}#features">{{ __('marketing.features') }}</a>
                        <a href="{{ route('docs') }}">{{ __('marketing.docs') }}</a>
                        <a href="#">{{ __('marketing.changelog') }}</a>
                    </div>
                </nav>
                <nav aria-label="Company">
                    <div class="footer-heading">{{ __('marketing.company') }}</div>
                    <div class="footer-links">
                        <a href="{{ route('blog') }}">{{ __('marketing.blog') }}</a>
                        <a href="https://github.com/Mohammadtrabelsi/MajidaStarter" target="_blank" rel="noopener">{{ __('marketing.github') }}</a>
                        <a href="https://opensource.org/licenses/MIT" target="_blank" rel="noopener">{{ __('marketing.license') }}</a>
                    </div>
                </nav>
            </div>
            <div class="container footer-bottom">
                <div class="text-muted ms-fs-12">© {{ date('Y') }} {{ config('app.name') }}.</div>
                <span class="footer-status">{{ __('marketing.in_memory') }}</span>
            </div>
        </footer>
    </body>
</html>
