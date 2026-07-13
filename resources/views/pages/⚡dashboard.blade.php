<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Dashboard')] class extends Component
{
    //
};
?>

<div class="mx-auto max-w-5xl space-y-6">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-fuchsia-600 p-8 text-white shadow-lg shadow-indigo-500/20">
        <h1 class="text-2xl font-bold">Welcome back, {{ Str::of(auth()->user()->name)->before(' ') }} 👋</h1>
        <p class="mt-2 max-w-xl text-indigo-100">
            You're logged in as <span class="font-medium text-white">{{ auth()->user()->email }}</span>.
            @if (auth()->user()->isAdmin())
                You have administrator privileges on this account.
            @endif
        </p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="mt-5 inline-flex items-center gap-2 rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/30 transition hover:bg-white/25">
                Go to Admin Panel
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400">Account</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Name</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ auth()->user()->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ auth()->user()->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Member since</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ auth()->user()->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400">Role</h2>
            <div class="mt-3 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ auth()->user()->isAdmin() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300' }}">
                    {{ auth()->user()->isAdmin() ? 'Administrator' : 'Member' }}
                </span>
            </div>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                @if (auth()->user()->isAdmin())
                    You can manage users from the admin panel.
                @else
                    Standard account with access to your personal dashboard.
                @endif
            </p>
        </div>
    </div>
</div>
