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
    <body class="antialiased" x-data="{ sidebarOpen: false }">
        <div class="shell">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="shell-overlay ms-hidden"
                @click="sidebarOpen = false"
            ></div>

            <aside class="shell-sidebar" :class="sidebarOpen && 'open'">
                <a href="{{ route('dashboard') }}" wire:navigate class="brand ms-pad-x20-b22">
                    <span class="brand-mark">{{ Str::substr(config('app.name', 'M'), 0, 1) }}</span>
                    <span class="brand-name">{{ config('app.name') }}</span>
                </a>

                <nav class="side-nav">
                    <a href="{{ route('dashboard') }}" wire:navigate class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9z"></path></svg>
                        {{ __('app.dashboard') }}
                    </a>

                    @if (auth()->user()?->isAdmin())
                        <div class="side-nav-label">{{ __('app.admin') }}</div>

                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="{{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3"></circle><path d="M5 21c0-4 3-6 7-6s7 2 7 6"></path></svg>
                            {{ __('app.users') }}
                        </a>

                        @can('manage posts')
                            <a href="{{ route('admin.posts.index') }}" wire:navigate class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 4h11l3 3v13a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"></path><path d="M8 10h8M8 14h8M8 18h5"></path></svg>
                                {{ __('app.posts') }}
                            </a>
                        @endcan

                        @can('manage categories')
                            <a href="{{ route('admin.categories.index') }}" wire:navigate class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h16M4 18h10"></path></svg>
                                {{ __('app.categories') }}
                            </a>
                        @endcan

                        @can('view activity log')
                            <a href="{{ route('admin.activity-log') }}" wire:navigate class="{{ request()->routeIs('admin.activity-log') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 12 8 12 11 5 14 19 17 12 21 12"></polyline></svg>
                                {{ __('app.activity_log') }}
                            </a>
                        @endcan

                        @can('manage settings')
                            <a href="{{ route('admin.settings') }}" wire:navigate class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="7"></circle><circle cx="12" cy="12" r="2.5"></circle></svg>
                                {{ __('app.settings') }}
                            </a>
                        @endcan
                    @endif
                </nav>

                <div class="side-user">
                    <span class="avatar">{{ Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => $p[0] ?? '')->take(2)->implode('') }}</span>
                    <div class="ms-minw-0">
                        <div class="ms-ellipsis-strong">{{ auth()->user()->name }}</div>
                        <div class="text-muted ms-fs-11">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Member' }}</div>
                    </div>
                </div>
            </aside>

            <div class="shell-main">
                <div class="shell-topbar">
                    <button type="button" class="topbar-burger" @click="sidebarOpen = true" aria-label="Open menu">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div class="ms-flex-1"></div>

                    @include('partials.locale-switcher')
                    @include('partials.theme-toggle')

                    <div class="ms-row-12-rel" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false" class="ms-iconbtn-plain">
                            <span class="avatar">{{ Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => $p[0] ?? '')->take(2)->implode('') }}</span>
                            <span class="hidden sm:inline ms-strong-13">{{ auth()->user()->name }}</span>
                            @if (auth()->user()->isAdmin())
                                <span class="tag tag-accent hidden sm:inline-flex">{{ __('app.admin') }}</span>
                            @endif
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            class="blueprint ms-menu-abs"
                        >
                            <div class="text-muted ms-menu-head">
                                {{ auth()->user()->email }}
                            </div>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="ms-menu-link">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3.2"></circle><path d="M5 20c0-3.3 3-5 7-5s7 1.7 7 5"></path></svg>
                                {{ __('app.profile') }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="ms-border-top">
                                @csrf
                                <button type="submit" class="ms-menu-btn-danger">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    {{ __('app.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <main class="shell-content">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
