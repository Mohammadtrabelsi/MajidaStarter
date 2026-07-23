<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Dashboard')] class extends Component
{
    //
};
?>

<div class="ms-mw-960">
    <div class="ms-mb-24">
        <div class="card-kicker">{{ __('dashboard.title') }}</div>
        <h2>{{ __('dashboard.welcome', ['name' => Str::of(auth()->user()->name)->before(' ')]) }}</h2>
        <p class="text-muted ms-fs-14-m0">
            {{ __('dashboard.logged_in_as', ['email' => auth()->user()->email]) }}
            @if (auth()->user()->isAdmin())
                {{ __('dashboard.admin_note') }}
            @endif
        </p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn btn-primary ms-mt-18">
                {{ __('dashboard.admin_panel') }}
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        @endif
    </div>

    <div class="dash-cards">
        <div class="card">
            <div class="card-kicker">{{ __('dashboard.account') }}</div>
            <dl class="ms-detail-list">
                <div class="ms-row-between-16">
                    <dt class="text-muted">{{ __('dashboard.name') }}</dt>
                    <dd class="ms-m0-strong">{{ auth()->user()->name }}</dd>
                </div>
                <div class="ms-row-between-16">
                    <dt class="text-muted">{{ __('dashboard.email') }}</dt>
                    <dd class="ms-m0-strong-ellipsis">{{ auth()->user()->email }}</dd>
                </div>
                <div class="ms-row-between-16">
                    <dt class="text-muted">{{ __('dashboard.member_since') }}</dt>
                    <dd class="ms-m0-strong">{{ auth()->user()->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="card">
            <div class="card-kicker">{{ __('dashboard.role') }}</div>
            <div class="ms-my-12">
                <span class="tag {{ auth()->user()->isAdmin() ? 'tag-accent' : 'tag-neutral' }}">
                    {{ auth()->user()->isAdmin() ? __('dashboard.administrator') : __('dashboard.member') }}
                </span>
            </div>
            <p class="text-muted ms-note">
                @if (auth()->user()->isAdmin())
                    {{ __('dashboard.admin_permissions_note') }}
                @else
                    {{ __('dashboard.member_note') }}
                @endif
            </p>
        </div>
    </div>
</div>
