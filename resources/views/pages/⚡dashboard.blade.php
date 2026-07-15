<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Dashboard')] class extends Component
{
    //
};
?>

<div style="max-width: 960px;">
    <div style="margin-bottom: 24px;">
        <div class="card-kicker">{{ __('dashboard.title') }}</div>
        <h2>Welcome back, {{ Str::of(auth()->user()->name)->before(' ') }}</h2>
        <p class="text-muted" style="font-size: 14px; margin: 0;">
            You're logged in as {{ auth()->user()->email }}.
            @if (auth()->user()->isAdmin())
                {{ __('dashboard.admin_note') }}
            @endif
        </p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn btn-primary" style="margin-top: 18px;">
                {{ __('dashboard.admin_panel') }}
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        @endif
    </div>

    <div class="dash-cards">
        <div class="card">
            <div class="card-kicker">{{ __('dashboard.account') }}</div>
            <dl style="margin: 12px 0 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <dt class="text-muted">{{ __('dashboard.name') }}</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ auth()->user()->name }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <dt class="text-muted">{{ __('dashboard.email') }}</dt>
                    <dd style="margin: 0; font-weight: 500; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->email }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <dt class="text-muted">{{ __('dashboard.member_since') }}</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ auth()->user()->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="card">
            <div class="card-kicker">{{ __('dashboard.role') }}</div>
            <div style="margin: 12px 0 10px;">
                <span class="tag {{ auth()->user()->isAdmin() ? 'tag-accent' : 'tag-neutral' }}">
                    {{ auth()->user()->isAdmin() ? __('dashboard.administrator') : __('dashboard.member') }}
                </span>
            </div>
            <p class="text-muted" style="font-size: 13px; margin: 0;">
                @if (auth()->user()->isAdmin())
                    {{ __('dashboard.admin_permissions_note') }}
                @else
                    {{ __('dashboard.member_note') }}
                @endif
            </p>
        </div>
    </div>

    <style>
        .dash-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 640px) { .dash-cards { grid-template-columns: 1fr; } }
    </style>
</div>
