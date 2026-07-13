<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-12 dark:bg-slate-950 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -left-32 h-96 w-96 rounded-full bg-indigo-400/30 blur-3xl dark:bg-indigo-600/20"></div>
                <div class="absolute top-1/2 -right-32 h-96 w-96 -translate-y-1/2 rounded-full bg-fuchsia-400/30 blur-3xl dark:bg-fuchsia-600/20"></div>
                <div class="absolute -bottom-40 left-1/3 h-96 w-96 rounded-full bg-sky-400/20 blur-3xl dark:bg-sky-600/20"></div>
            </div>

            <div class="relative w-full max-w-md">
                <div class="mb-8 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-lg font-bold text-white shadow-lg shadow-indigo-500/30">
                            {{ Str::substr(config('app.name', 'Laravel'), 0, 1) }}
                        </span>
                        <span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-sm dark:border-white/10 dark:bg-slate-900/70 dark:shadow-black/20">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
